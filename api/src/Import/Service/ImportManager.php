<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 **************************/

namespace App\Import\Service;

use App\Carpool\Repository\ProposalRepository;
use App\Carpool\Service\ProposalManager;
use App\Communication\Entity\Medium;
use App\Communication\Repository\NotificationRepository;
use App\Import\Entity\UserImport;
use App\Import\Repository\UserImportRepository;
use App\Right\Entity\Role;
use App\Right\Entity\UserRole;
use App\Right\Repository\RoleRepository;
use App\User\Entity\UserNotification;
use App\User\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use App\User\Entity\User;

/**
 * Import manager service.
 * Used to import external data into the platform.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class ImportManager
{
    private $entityManager;
    private $userImportRepository;
    private $proposalManager;
    private $userManager;
    private $roleRepository;
    private $notificationRepository;
    private $proposalRepository;
   
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, UserImportRepository $userImportRepository, ProposalRepository $proposalRepository, ProposalManager $proposalManager, UserManager $userManager, RoleRepository $roleRepository, NotificationRepository $notificationRepository)
    {
        $this->entityManager = $entityManager;
        $this->userImportRepository = $userImportRepository;
        $this->proposalManager = $proposalManager;
        $this->userManager = $userManager;
        $this->roleRepository = $roleRepository;
        $this->notificationRepository = $notificationRepository;
        $this->proposalRepository = $proposalRepository;
    }

    /**
     * Treat imported users
     *
     * @return array    The users imported
     */
    public function treatUserImport()
    {
        set_time_limit(3600);
        //gc_enable();
        
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

        // // create user_import rows
        // $conn = $this->entityManager->getConnection();
        // $sql = "INSERT INTO user_import (user_id, user_external_id,origin,status,created_date) SELECT id as userid, id as extuserid, 'ouestgo', 0, '" . (new \DateTime())->format('Y-m-d') . "' FROM user";
        // $stmt = $conn->prepare($sql);
        // $stmt->execute();

        // // update proposal : set private to 0 if initialized to null
        // $sql = "UPDATE proposal SET private = 0 WHERE private is null";
        // $stmt = $conn->prepare($sql);
        // $stmt->execute();

        // we have to treat all the users that have just been imported
        // first pass : update status before treatment
        $q = $this->entityManager
        ->createQuery('UPDATE App\Import\Entity\UserImport u set u.status = :status, u.treatmentUserStartDate=:treatmentDate WHERE u.status=:oldStatus')
        ->setParameters([
            'status'=>UserImport::STATUS_USER_PENDING,
            'treatmentDate'=>new \DateTime(),
            'oldStatus'=>UserImport::STATUS_IMPORTED
        ]);
        $q->execute();

        //gc_collect_cycles();

        // second pass : user related treatment
        $batch = 50;    // batch for users
        $pool = 0;
        $qCriteria = $this->entityManager->createQuery('SELECT u FROM App\User\Entity\User u JOIN u.import i WHERE i.status='.UserImport::STATUS_USER_PENDING);
        $iterableResult = $qCriteria->iterate();
        foreach ($iterableResult as $row) {
            $user = $row[0];

            // we treat the role
            if (count($user->getUserRoles()) == 0) {
                // we have to add a role
                $role = $this->roleRepository->find(Role::ROLE_USER_REGISTERED_FULL); // can't be defined outside the loop because of the flush/clear...
                $userRole = new UserRole();
                $userRole->setRole($role);
                $user->addUserRole($userRole);
            }

            // we treat the notifications
            if (count($user->getUserNotifications()) == 0) {
                // we have to create the default user notifications, we don't persist immediately
                $notifications = $this->notificationRepository->findUserEditable(); // can't be defined outside the loop because of the flush/clear...
                foreach ($notifications as $notification) {
                    $userNotification = new UserNotification();
                    $userNotification->setNotification($notification);
                    $userNotification->setActive($notification->isUserActiveDefault());
                    if ($userNotification->getNotification()->getMedium()->getId() == Medium::MEDIUM_SMS && is_null($user->getPhoneValidatedDate())) {
                        // check telephone for sms
                        $userNotification->setActive(false);
                    } elseif ($userNotification->getNotification()->getMedium()->getId() == Medium::MEDIUM_PUSH && is_null($user->getIosAppId()) && is_null($user->getAndroidAppId())) {
                        // check apps for push
                        $userNotification->setActive(false);
                    }
                    $user->addUserNotification($userNotification);
                }
            }
            //$this->entityManager->persist($user);

            // batch
            $pool++;
            if ($pool>=$batch) {
                $this->entityManager->flush();
                $this->entityManager->clear();
                //gc_collect_cycles();
                $pool = 0;
            }
        }
        // final flush for pending persists
        $this->entityManager->flush();
        $this->entityManager->clear();
        //gc_collect_cycles();

        $q = $this->entityManager
        ->createQuery('UPDATE App\Import\Entity\UserImport u set u.status = :status WHERE u.status=:oldStatus')
        ->setParameters([
            'status'=>UserImport::STATUS_USER_TREATED,
            'oldStatus'=>UserImport::STATUS_USER_PENDING
        ]);
        $q->execute();

        // batch for criterias / direction
        $batch = 50;
        $this->proposalManager->setDirectionsAndDefaultsForImport($batch);

        // update addresses with geojson point data
        $conn = $this->entityManager->getConnection();
        $sql = "UPDATE address SET geo_json = PointFromText(CONCAT('POINT(',longitude,' ',latitude,')'),1) WHERE geo_json IS NULL";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $q = $this->entityManager
        ->createQuery('UPDATE App\Import\Entity\UserImport u set u.status = :status, u.treatmentUserStartDate=:treatmentDate WHERE u.status=:oldStatus')
        ->setParameters([
            'status'=>UserImport::STATUS_DIRECTION_TREATED,
            'treatmentDate'=>new \DateTime(),
            'oldStatus'=>UserImport::STATUS_USER_TREATED
        ]);
        $q->execute();

        //gc_collect_cycles();

        return [];
    }

    /**
     * Match imported users
     *
     * @return array    The users imported
     */
    public function matchUserImport()
    {
        set_time_limit(50000);
        
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

        // update addresses with geojson point data
        $conn = $this->entityManager->getConnection();
        
        for ($i=0;$i<=500;$i++) {
            $sql = "INSERT INTO `direction` (`distance`, `duration`, `ascend`, `descend`, `bbox_min_lon`, `bbox_min_lat`, `bbox_max_lon`, `bbox_max_lat`, `detail`, `format`, `snapped`, `bearing`) VALUES
        (189038, 7460, NULL, NULL, '-4.485758', '47.656870', '-2.761161', '48.419489', 'gd{aH|lzOF_A?U}@e@EIEMeB?CbDC|@Ct@Ef@GVO^ITe@l@KX@jADREZ@z@BnADx@BlE@fHAV?pK@bF@pACrBC^?tBInCOvFAd@C~@MjECjASdHInCObGA`@IvAQHOZCR@ZBLLNFj@Bd@K~DGl@ShI]tNQpHGhDAp@Kr@IDKRCJ?\\BJNV@XG|Be@lEGHCH@VDFFjFGrB?n@M`BOn@Yh@iAxA_AjAGNMb@In@Er@BXJj@tAnDFRFh@HdBXdFJxFDdGRvaA@~KAbJK~JO~HeAh^KhEKbIG`IA`GBtKTxe@lArmBTv]JhNAlGErCGhBi@zIwBr[{O`~BmBpXgFrt@WrEGxACtD@|BNdHB`CC`CIdCQjDUlCS~Ac@`C]`Bw@zC{@hCuQtc@u@rBqDxKm@|B]~Ao@`Ec@lEUvFAp@BdQB|CFrBLpCPvBTdCThBlIbk@dCnPnAdHtArGp@nCjBzG|@nC`AnCxBxFlDnIzSzg@fBzEnAlEXhA\\|Ar@lD^fC\\jCb@rEZ`FNnC~@rSjArWZnF^vDh@xD~@`FzDxPfA~FXtB\\~CXfDTnDDbBFlBD`EApCMlGMtCQbCOjBi@xEaExYaA~FiAzE{@vCy@`CkAnCeAvBoAzBsAnBgAtA_BfBoAjAoBzAcAp@yAx@{Ap@iL`EuFjBcDvAoAn@qAv@}B`BgKrI_DvBaCpAwClA}C|@kJ|BqBp@{CnA_CpAuCnBuCbC}A`B{@`AgB`C_ClDmAzBoAfC{JfUgD~HiAbCqAzBs@fAgAzA}AfBkAlA{ApA_An@sBlAmc@tU{FpDuCpBaBlAkFfEiC~BkCdCkGrG_EtE{BnCan@lw@gd@lk@kC|DeAhBeBpD{@|BaAvCaBrFwBtHs@|BoHfWmFnRyRjt@kAbEy@lCeBxEq@dBaAzBuCnFuBbDmBjCeAlAu@t@}A`ByBpBuC~B{M|Joy@rn@yAjAeDvC}[dZeBdB{CpDiCtDeAdBsAbCwAxCoA~CsAnDsAnE}@nDm@lCe@|By@fF[~B_@fDgGzo@{@rHk@xDw@xD}@vDsAxEcApCiHnRkDhJkE~LaCfIuBvIcAzEkAlGkA~Hi@bEqOrqA_CxQiAfI_BhK{DhU{D`SmA|F}DrQeDtNmD~NiCdKqDbNsBjHo@xBaGnRgEbM_FfNIPyHrTsD~LsCrK{BrJgBzI_BbJiAnHuA`KmArKw@pI}@lL_@nGm@zNYpJuCzfAGjB_@fN]vJg@bLo@lMcArSiCfh@a@vIQzEYxLGrI@zJFtFHfDZ|JL|CZpFJfB|Bd]HzA\\xFLfFB|A@vEEjEQxEc@xGsCd]u@rIsAxOc@lFe@rF_BzQY~BsA~I_AfFSnAq@fEe@bESrBMvA[rGMrGAzA?nAH|GPnEJbB`@lFb@zDt@dF`@vBdAdF~Hv]pKfe@hM|i@fAvDf@rA~AtDhAtBfAfBnD`FzBjDnA|BjAbCt@`BtB|FpArExArGf@|Cd@|Cl@vFVvCBl@~B|\\d@tF~@fJxAhJbAnFd@xBXlA^`BxHpZ~EhRhFrSlApFh@hCb@jCXnBf@hFZ~FDfBB|EGnEEhBSlDOdBO|Ag@xDa@xB_@rBg@pBq@|Ba@nAiAxCsBrEwHxNaBjDa@~@gBvEm@jBoAjE}ArHy@lFm@rF_@jFUfFIbF?bFD`ELfHDdBTjKDfBNxKArDGtDSjFMbCe@lF[hCe@zCi@|C]bBmA~EeBrFcAlCw@hBcBfD{@~AyA~BkRvYoD|FcClEaCjF{@nBw@rB_BrEkAvDm@zBqBhI{Hn]aBpGiAtDiBhFuBjF{AdD_B|CuAdCwAzB}CnE_BpBaBlB_CbCyClCoCtBgBnAmFzC{BdAkFnBqExAyb@dNsMfEgPjFyF`CaGrC}IhFq@h@mJlHuGtG_EpEqGpI_EhG{a@ps@}FbKy^xn@kGzKmWtc@cMjTqD|GqAlCuA~C{A`EiAbDiBbGmEvPyDtN}@xCwBjG_ElJa@t@{MlW}Qr\\wC`HeBpEeCfKkA`HU`BaAtIgFth@{BbU_CrUe@`GY~EKdEI|EA|JJpUJpf@ClDQ~I[bIQ`Du@`KmA~K}EhZgFjVeEfScA~EiErSqDbQuNdr@uFrZmG~a@iD|Z_B~OoB`Vo@bJ_Cta@k@lQe@lQm@n[Ap^LlVXjZDhKIfL_@fLWrGa@pGy@|I}@zIaF`YyVlpAmDlRw@bF}DvYoBvSeAnN]|Ec@pIa@nJi@lSKlHEdHCvEFbUPbMNnF`A`YvEzeAx@|Rj@fNnApYd@fQDbGBfJAvEWvMe@nLk@zJo@~HmJ|`AyAxPm@tIk@jJ_@|GeA`ZsBleAQfFa@xG}@nKq@jFu@dFm@jDe@xBaAnEcBzGsC~J{BxHaAnDqC`LwBdKaBtJs@nE_BfNo@vGs@zIoE~k@cA`M{@~I}A|LyB|M}B~KaB|GmBzGw@nC{AnEyDhKeCjGkMzZwB~F}BhHcCvJgAhFg@xCw@xEs@lFeDhWmAlHu@~Dy@tDkAjEmBrGiAhDiAxC_AzBsBhEmBrDkCpEkChE}IfNcCpEyAvCeD`Iq@fBiAdDuAxE{EjSqErSsDhPw@`D{@|CyAtEwB~FwBbFoBzDs@lAcDvFgF~Ga@f@yG|HoGxI_BjCgDfGaCrE_JvO_IjMeEfHmBzDiDnIoB~FgBtGkJh^sFhUyIj[mEtMuFfOwApDoElKqCfGyDbI}D|HuCdFaG|JeExGg@r@_GlIwJ|NeE~Hk@jAgBfEoDvJyCvJy@tC_EzMgKz]oAxEcEzPS`AaBvHqBxJkBfKw@vEYjBkCzQyAjLeDvXePtsAk@zDy@fE{@tDaAhD_DjIoCtFeAfBuEnFeBdB}FtEiDjCeOfLaC~BgAnAy@hAk@bA}AdDq@hBgAzDsCnLy@pCs@pBcBrDs@lAiJlMeAhBi@fA}@vBaAvC{@nDYzAc@fDSxBOtCIhDEfEC~OIvHG|BMjCU|COvAYlBcAbFm@xB{@tCsAbDgAxBq@lAmBpCeBvBg@b@e@b@qA|@oAn@q]vScBlAaCvBo@r@iB|Bg@t@aAbBcAxBuBfFaBxFqEbR_@pAi@dB_CrGkClFeAjBcF`H{@fAa@h@gGbI_BhCaCrEaCzFwVfp@aAhBo@lA[t@q@`Aa@NUASGQOQ[Km@?i@Ho@HSTUTK^?\\TBJNNPA`@Jj@l@h@ZPLl@Hl@Cx@Iv@GREb@?TFTNBTCR[|@yBbEGJw@jBa@~@kBdEaAnBYR_@HQGODMNGX@`@FLJJHXB^GpAKnAAHH@??LyAFkBBWDMDGJ]?KAMIUEIQGODMNGXg@j@MJUHk@TWJqAj@{BjAmBbAgAb@q@`@_@Na@FGAGISKUBIDWKKKyBcD_AwA[]m@g@e@S{@Oi@AgAXa@Te@f@s@dAsBlDm@t@oAbAyChBm@f@cE~BeDpA}Bh@yBR{A@aCMg@EyBc@iA_@u@]mDmBkSgMmCiBeAy@yAuAcAgAsAaB{A_C}@_By@cBiAkCsJmW}AmD}@iB_AaBU[s@aAqCgDeAcAqAgAiBqAmDyBeEaCwJkG{@q@wAmAyA{As@y@sBwCeAiBeAsBy@iBu@mBeKcXu@iBkA}BqAuBo@_AOSaBsBwCwCsB{AyA}@{Au@g{@k`@cDsA{CkAmBo@eCs@qFsAyr@sOwNgD_GeB{GeC}E}BoEcC_EgCsJqHwSsQ}EmEQOaDuCuEoDeC}AqC{AmCgAuAe@kG_BsDi@m@I{H{@kDe@kC_@oBc@}DkAkDqAyv@s[oPyGcF_BkD{@cEw@wASqGo@iAIsFK}FFoFZeEd@cEr@sEfAcAXmEzAaA`@qLpFoJrEu~Avv@wf@~UqDnBcCtAmCdBgEbDcB|AiGnGqFdGyGlHuAlAeAv@eAn@oAl@cBl@}A\\kARwBNyAAaACy@GiBYeAUoAa@q@[mBgA_Ao@wNcLqA{@eB_AsDiAuAO_BIeDJeBTs@Lk@RqCnA_KvFiFtCcDlBcAb@yC~@w@N{CViCEw@E}B_@qEoAkHaCmEoAsDw@{Ce@kCWwIk@s`@_CcDOcGKeEB}@B{CRuCXuEx@eCj@{Ab@_FdB}CvA{BnAkAp@cC`BcCjBy@r@iD~CsBzBeCxCgEdGyBjDwAvC_F|KiCpGcHnRoSpk@}JbY_CvHiB`HuA~F}AhIgBdLoBrNm@bFsFpa@eAfHcAhGq@hDyBpJgB~GOn@yB~IsBzHcB`GmBnFu@hBy@fBgBvCuBjCeCvBgAx@o@`@oB~@eA`@YHcA\\iBb@sEx@uWrDwF~@aATgC|@uAt@}@l@mA`AyBtBkGtGgPtPsChC_BbA{At@oAd@o@R_AReBVsAFcA@qAEwBOsHgAyGeAgF{@kHcAcCKy@?}BNmAPcB`@wAh@aBz@qAx@uEdDcDdCeCzAkB~@eBn@gA\\eB`@eANa`@lEsDj@sA\\}@\\u@\\cB`AgBvAs@n@u@x@{AtBa@p@aAjBu@fBYr@q@~BcAdESlAa@~CSdCQvCgDn{@QjCWjDe@hEWrB}@nFyFrX}Hd_@i@pCWxA_@vByEr\\aAtG_AlE{@vCuAnDmBpDyCbEqQvQkErEsCbDqCjD_GlIeAbBS\\{AdCaHjNcBvDyF`O}BnHyBnHsE~QiAfFkA|EwA`FqFtP_BdEiC~FaBnDiDjGwDhG{@tAeBhD}AhDqAvDiA`Eq@tCgAtFwFjXkJpe@qBzI{@hDaAhDuAjE_AxBeBvDaAhB_A|AiAbBaD~DsA~A_BzA}DhCwRbLoP|IgKfGkHnDkDtA{C~@oDv@wEr@eE`@uWvBoPxAyCb@sDp@gEhAyDtAaElB}DvB}B|AyBbBkC|BeCfCkEfFuBtCyAxBaC~D}EdJuBlEkTff@yG~NcB`DkAfBwAfBaA~@q@n@_C`BkAn@mLvFeAj@_Al@qBxAo@j@sC~CoC~Ds@pA]p@{@~Ae@hAkAbDmA|DaEzNoA`E_@~@k@hAs@hAq@z@aGvEi@l@c@j@o@jA]x@kC|Jk@zA[j@a@n@yBhCyAnBg@z@eArCkAtFeEd^oHvn@oC|TwAhNyAxOcAfKi@|Eq@vFmCpQyArJoBfNyB|QeBrNa@|FSnDGvCCzFBvBJxCzA|XVnF`@vMn@`\\D~Er@p^NrLFhDB~IAjHE`HMfMWfM{@hW_@xJu@|YQbOC`Rf@|k@@~KEfCSzQc@lWGrB}@lRGvFFnFd@nODdCEfBI~ASrBg@`DSz@i@fB_@~@gApBk@t@yVdZgI~JaDvD{GhIaClDcBjDoA|Ca@v@w@dAaBdBmAn@yDfBuAf@kCj@YJi@`@uAxAUCSDQLKRIVCXOf@OVSNe@T{GdEsCnBaCxAiBt@}@^g@R_[nHkDxA}CxBsBzBmBtC{BrDg@h@QKSCQ@UHQPMXI^Ab@B`@F\\LVNPTJV@TGRONY~@f@bATvBQLCxAM~AOnE_@rE]j@?bCJcCKk@?sE\\oE^_BNyALMBwBPcAU_Ag@J]B_@Aa@E_@M[QUQKSCQ@UHQPMXI^Ab@B`@}@`B{CdEiN~UsBpFiAlDy@pDi@rC{@zFa@|EUdDaAjOw@rJq@pFk@xIQ|AOJKNQn@Ch@Fl@J\\PTLb@@n@aArOMh@QPERAPBVPT?^GvAWrGEfFBlGT`HN|Bn@fHDbA?|AFj@Z|A`@zBb@fBjA~Fr@|DPz@t@|Dr@`ETnBv@jFXlB@`@FpCBlEH~ADlDLpH?rABnB@hBLjCBz@VzDFl@L~BLvBCPOd@H|A\\VD|@B~@N`A@^NnD@HJ|BTnECPHLj@tGZtCb@pCRlBQZM`@Gf@?f@Dh@H\\LXPTRLTHX?XIVQ\\m@n@@RHzCvAj@VpAn@hGnDxChBRLfBzALX@PH\\PRJDJ^@^AvBJjD@^C\\\\Gl@G`Hs@vFc@tAQ`@EdE]jAKdAGD|ABhABlAqAHC{C', 'GraphHopper', 'gd{aH|lzOgydAdbaGgjkA|n`AlK`}K', 302);
        ";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
        }
        
        // creation of the matchings
        // we create an array of all proposals to treat
        // $proposalIds = $this->proposalRepository->findImportedProposals(UserImport::STATUS_DIRECTION_TREATED);

        // $this->proposalManager->createMatchingsForProposals($proposalIds);

        // // treat the return and opposite
        // $proposals = $this->proposalManager->createLinkedAndOppositesForProposals($proposalIds);

        return [];
    }
}
