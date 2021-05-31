<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace App\User\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use App\Action\Entity\Diary;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Geography\Entity\Address;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Ask;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use App\Match\Entity\Mass;
use App\Image\Entity\Image;
use App\Communication\Entity\Message;
use App\Communication\Entity\Recipient;
use App\User\Controller\UserRegistration;
use App\User\Controller\UserDelegateRegistration;
use App\User\Controller\UserPermissions;
use App\User\Controller\UserAlerts;
use App\User\Controller\UserAlertsUpdate;
use App\User\Controller\UserLogin;
use App\User\Controller\UserAsks;
use App\User\Controller\UserThreads;
use App\User\Controller\UserThreadsDirectMessages;
use App\User\Controller\UserThreadsCarpoolMessages;
use App\User\Controller\UserThreadsSolidaryMessages;
use App\User\Controller\UserUpdatePassword;
use App\User\Controller\UserGeneratePhoneToken;
use App\User\Controller\UserUpdate;
use App\User\Controller\UserDelete;
use App\User\Controller\UserCheckPhoneToken;
use App\User\Controller\UserUnsubscribeFromEmail;
use App\User\Controller\UserMe;
use App\User\Filter\HomeAddressTerritoryFilter;
use App\User\Filter\DirectionTerritoryFilter;
use App\User\Filter\IsInCommunityFilter;
use App\User\Filter\ProposalValidFilter;
use App\User\Filter\ODRangeDestinationFilter;
use App\User\Filter\ODRangeOriginFilter;
use App\User\Filter\ODRangeRadiusFilter;
use App\User\Filter\HomeAddressDirectionTerritoryFilter;
use App\User\Filter\ODTerritoryFilter;
use App\User\Filter\WaypointTerritoryFilter;
use App\User\Filter\HomeAddressODTerritoryFilter;
use App\User\Filter\HomeAddressWaypointTerritoryFilter;
use App\User\Filter\FamilyAndGivenNameFilter;
use App\User\Filter\LoginFilter;
use App\User\Filter\PwdTokenFilter;
use App\User\Filter\SolidaryFilter;
use App\User\Filter\SolidaryCandidateFilter;
use App\User\Filter\EmailTokenFilter;
use App\User\Filter\UnsubscribeTokenFilter;
use App\Communication\Entity\Notified;
use App\Action\Entity\Log;
use App\App\Entity\App;
use App\Auth\Entity\AuthItem;
use App\Import\Entity\UserImport;
use App\MassCommunication\Entity\Campaign;
use App\MassCommunication\Entity\Delivery;
use App\Auth\Entity\UserAuthAssignment;
use App\Carpool\Entity\CarpoolProof;
use App\Solidary\Entity\Solidary;
use App\User\EntityListener\UserListener;
use App\Event\Entity\Event;
use App\Community\Entity\CommunityUser;
use App\Match\Entity\MassPerson;
use App\Payment\Ressource\BankAccount;
use App\Solidary\Entity\Operate;
use App\Solidary\Entity\SolidaryUser;
use App\User\Controller\UserCanUseEmail;
use App\User\Controller\UserSendValidationEmail;

/**
 * A user.
 *
 * Note : force eager is set to false to avoid max number of nested relations (can occure despite of maxdepth... https://github.com/api-platform/core/issues/1910)
 *
 * @ORM\Entity
 * @ORM\Table(indexes={@ORM\Index(name="IDX_NEWS_SUBSCRIPTION", columns={"news_subscription"})})
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity("email")
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readUser","mass","readSolidary","userStructure", "readExport","carpoolExport"}, "enable_max_depth"="true","skip_null_values"="false"},
 *          "denormalization_context"={"groups"={"write","writeSolidary"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "normalization_context"={"groups"={"readUser"}},
 *              "security"="is_granted('user_list',object)"
 *          },
 *          "checkEmail"={
 *              "method"="GET",
 *              "path"="/users/checkEmail",
 *              "security_post_denormalize"="is_granted('user_register',object)"
 *          },
 *          "checkPasswordToken"={
 *              "method"="GET",
 *              "path"="/users/checkPasswordToken",
 *              "security_post_denormalize"="is_granted('user_register',object)"
 *          },
 *          "post"={
 *              "method"="POST",
 *              "path"="/users",
 *              "normalization_context"={"groups"={"readUser"}},
 *              "swagger_context" = {
 *                  "parameters" = {
 *                      {
 *                          "name" = "givenName",
 *                          "type" = "string",
 *                          "required" = true,
 *                          "description" = "User's given name"
 *                      },
 *                      {
 *                          "name" = "familyName",
 *                          "type" = "string",
 *                          "required" = true,
 *                          "description" = "User's family name"
 *                      },
 *                      {
 *                          "name" = "email",
 *                          "type" = "string",
 *                          "required" = true,
 *                          "description" = "User's email"
 *                      },
 *                      {
 *                          "name" = "password",
 *                          "type" = "string",
 *                          "required" = true,
 *                          "description" = "Clear version of the password"
 *                      },
 *                      {
 *                          "name" = "gender",
 *                          "type" = "int",
 *                          "enum" = {1,2,3},
 *                          "required" = true,
 *                          "description" = "User's gender (1 : female, 2 : male, 3 : other)"
 *                      },
 *                      {
 *                          "name" = "birthDate",
 *                          "type" = "string",
 *                          "format" = "date",
 *                          "required" = true,
 *                          "example" = "1997-08-14T00:00:00+00:00",
 *                          "description" = "User's birthdate"
 *                      },
 *                      {
 *                          "name" = "userDelegate",
 *                          "type" = "string",
 *                          "required" = false,
 *                          "description" = "User IRI that creates the new user"
 *                      },
 *                      {
 *                          "name" = "passwordSendtype",
 *                          "type" = "int",
 *                          "enum" = {0,1,2},
 *                          "required" = true,
 *                          "description" = "Password send type (0 : none, 1 : sms, 2 : email)"
 *                      }
 *                  }
 *              },
 *              "security_post_denormalize"="is_granted('user_create',object)"
 *          },
 *          "userRegistration"={
 *              "method"="POST",
 *              "path"="/users/register",
 *              "normalization_context"={"groups"={"readUser"}},
 *              "swagger_context" = {
 *                  "parameters" = {
 *                      {
 *                          "name" = "givenName",
 *                          "type" = "string",
 *                          "required" = true,
 *                          "description" = "User's given name"
 *                      },
 *                      {
 *                          "name" = "familyName",
 *                          "type" = "string",
 *                          "required" = true,
 *                          "description" = "User's family name"
 *                      },
 *                      {
 *                          "name" = "email",
 *                          "type" = "string",
 *                          "required" = true,
 *                          "description" = "User's email"
 *                      },
 *                      {
 *                          "name" = "password",
 *                          "type" = "string",
 *                          "required" = true,
 *                          "description" = "Clear version of the password"
 *                      },
 *                      {
 *                          "name" = "gender",
 *                          "type" = "int",
 *                          "enum" = {1,2,3},
 *                          "required" = true,
 *                          "description" = "User's gender (1 : female, 2 : male, 3 : other)"
 *                      },
 *                      {
 *                          "name" = "birthDate",
 *                          "type" = "string",
 *                          "format" = "date",
 *                          "required" = true,
 *                          "example" = "1997-08-14T00:00:00+00:00",
 *                          "description" = "User's birthdate"
 *                      }
 *                  }
 *              },
 *              "security_post_denormalize"="is_granted('user_register',object)"
 *          },
 *          "checkPhoneToken"={
 *              "method"="POST",
 *              "denormalization_context"={"groups"={"checkPhoneToken"}},
 *              "normalization_context"={"groups"={"readUser"}},
 *              "path"="/users/checkPhoneToken",
 *              "controller"=UserCheckPhoneToken::class
 *          },
 *          "me"={
 *              "normalization_context"={"groups"={"readUser"}},
 *              "method"="GET",
 *              "path"="/users/me",
 *              "read"="false"
 *          },
 *          "paymentProfile"={
 *              "normalization_context"={"groups"={"readPayment"}},
 *              "method"="GET",
 *              "path"="/users/paymentProfile",
 *              "read"="false"
 *          },
 *          "accessAdmin"={
 *              "normalization_context"={"groups"={"readUser","readUserAdmin"}},
 *              "method"="GET",
 *              "path"="/users/accesFromAdminReact",
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/users",
 *              "method"="GET",
 *              "normalization_context"={
 *                  "groups"={"aRead"},
 *                  "skip_null_values"=false
 *              },
 *              "security"="is_granted('admin_user_list',object)"
 *          },
 *          "ADMIN_post"={
 *              "path"="/admin/users",
 *              "method"="POST",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_user_create',object)"
 *          },
 *          "ADMIN_associate_campaign"={
 *              "path"="/admin/users/associate-campaign",
 *              "method"="GET",
 *              "normalization_context"={
 *                  "groups"={"aRead"},
 *                  "skip_null_values"=false
 *              },
 *              "security"="is_granted('admin_user_list',object)"
 *          },
 *          "ADMIN_send_campaign"={
 *              "path"="/admin/users/send-campaign",
 *              "method"="GET",
 *              "normalization_context"={
 *                  "groups"={"aRead"},
 *                  "skip_null_values"=false
 *              },
 *              "security"="is_granted('admin_user_list',object)"
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "normalization_context"={"groups"={"readUser"}},
 *              "security"="is_granted('user_read',object)"
 *          },
 *          "password_update_request"={
 *              "method"="POST",
 *              "path"="/users/password_update_request",
 *              "controller"=UserUpdatePassword::class,
 *              "defaults"={"name"="request"},
 *              "read"=false,
 *              "denormalization_context"={"groups"={"passwordUpdateRequest"}},
 *              "normalization_context"={"groups"={"passwordUpdateRequest"}},
 *              "security"="is_granted('user_register',object)"
 *          },
 *          "password_update"={
 *              "method"="POST",
 *              "path"="/users/password_update",
 *              "controller"=UserUpdatePassword::class,
 *              "defaults"={"name"="update"},
 *              "read"=false,
 *              "denormalization_context"={"groups"={"passwordUpdate"}},
 *              "normalization_context"={"groups"={"passwordUpdate"}},
 *              "security"="is_granted('user_register',object)"
 *          },
 *          "generate_phone_token"={
 *              "method"="GET",
 *              "path"="/users/{id}/generate_phone_token",
 *              "controller"=UserGeneratePhoneToken::class,
 *              "security"="is_granted('user_update',object)"
 *          },
 *          "send_validation_email"={
 *              "method"="GET",
 *              "path"="/users/{id}/sendValidationEmail",
 *              "controller"=UserSendValidationEmail::class,
 *              "security"="is_granted('user_update',object)"
 *          },
 *          "alerts"={
 *              "method"="GET",
 *              "normalization_context"={"groups"={"alerts"}},
 *              "controller"=UserAlerts::class,
 *              "path"="/users/{id}/alerts",
 *              "security"="is_granted('user_read',object)"
 *          },
 *          "putAlerts"={
 *              "method"="PUT",
 *              "normalization_context"={"groups"={"alerts"}},
 *              "denormalization_context"={"groups"={"alerts"}},
 *              "path"="/users/{id}/alerts",
 *              "controller"=UserAlertsUpdate::class,
 *              "security"="is_granted('user_update',object)"
 *          },
 *          "threadsOBSOLETE20200311"={
 *              "method"="GET",
 *              "normalization_context"={"groups"={"threads"}},
 *              "controller"=UserThreads::class,
 *              "path"="/users/{id}/threads",
 *              "security"="is_granted('user_read',object)"
 *          },
 *          "threadsDirectMessages"={
 *              "method"="GET",
 *              "normalization_context"={"groups"={"threads"}},
 *              "path"="/users/{id}/threadsDirectMessages",
 *              "security"="is_granted('user_read',object)"
 *          },
 *          "threadsCarpoolMessages"={
 *              "method"="GET",
 *              "normalization_context"={"groups"={"threads"}},
 *              "path"="/users/{id}/threadsCarpoolMessages",
 *              "security"="is_granted('user_read',object)"
 *          },
 *          "threadsSolidaryMessages"={
 *              "method"="GET",
 *              "normalization_context"={"groups"={"threads"}},
 *              "path"="/users/{id}/threadsSolidaryMessages",
 *              "security"="is_granted('user_read',object)"
 *          },
 *          "put"={
 *              "method"="PUT",
 *              "path"="/users/{id}",
 *              "normalization_context"={"groups"={"readUser"}},
 *              "denormalization_context"={"groups"={"write"}},
 *              "security"="is_granted('user_update',object)"
 *          },
 *          "delete_user"={
 *              "method"="DELETE",
 *              "path"="/users/{id}",
 *              "controller"=UserDelete::class,
 *              "security"="is_granted('user_delete',object)"
 *          },
 *          "asks"={
 *              "method"="GET",
 *              "path"="/users/{id}/asks",
 *              "controller"=UserAsks::class,
 *              "security"="is_granted('user_read',object)"
 *          },
 *          "unsubscribe_user"={
 *              "method"="PUT",
 *              "path"="/users/{id}/unsubscribe_user",
 *              "controller"=UserUnsubscribeFromEmail::class
 *          },
 *          "getCarpoolExport"={
 *              "method"="GET",
 *              "path"="/users/{id}/carpool_export",
 *              "normalization_context"={"groups"={"carpoolExport"}},
 *              "security"="is_granted('user_update',object)"
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/users/{id}",
 *              "method"="GET",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "security"="is_granted('admin_user_read',object)"
 *          },
 *          "ADMIN_patch"={
 *              "path"="/admin/users/{id}",
 *              "method"="PATCH",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_user_update',object)"
 *          },
 *          "ADMIN_delete"={
 *              "path"="/admin/users/{id}",
 *              "method"="DELETE",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_user_delete',object)"
 *          },
 *      }
 * )
 * @ApiFilter(NumericFilter::class, properties={"id","gender"})
 * @ApiFilter(SearchFilter::class, properties={"email":"partial", "givenName":"partial", "familyName":"partial", "geoToken":"exact","telephone" : "exact"})
 * @ApiFilter(FamilyAndGivenNameFilter::class, properties={"q"})
 * @ApiFilter(HomeAddressTerritoryFilter::class, properties={"homeAddressTerritory"})
 * @ApiFilter(DirectionTerritoryFilter::class, properties={"directionTerritory"})
 * @ApiFilter(IsInCommunityFilter::class)
 * @ApiFilter(ProposalValidFilter::class)
 * @ApiFilter(ODRangeDestinationFilter::class)
 * @ApiFilter(ODRangeOriginFilter::class)
 * @ApiFilter(ODRangeRadiusFilter::class)
 * @ApiFilter(HomeAddressDirectionTerritoryFilter::class, properties={"homeAddressDirectionTerritory"})
 * @ApiFilter(HomeAddressODTerritoryFilter::class, properties={"homeAddressODTerritory"})
 * @ApiFilter(HomeAddressWaypointTerritoryFilter::class, properties={"homeAddressWaypointTerritory"})
 * @ApiFilter(ODTerritoryFilter::class, properties={"oDTerritory"})
 * @ApiFilter(WaypointTerritoryFilter::class, properties={"waypointTerritory"})
 * @ApiFilter(LoginFilter::class, properties={"login"})
 * @ApiFilter(PwdTokenFilter::class, properties={"pwdToken"})
 * @ApiFilter(UnsubscribeTokenFilter::class, properties={"unsubscribeToken"})
 * @ApiFilter(EmailTokenFilter::class, properties={"emailToken"})
 * @ApiFilter(SolidaryFilter::class, properties={"solidary"})
 * @ApiFilter(BooleanFilter::class, properties={"solidaryUser.volunteer","solidaryUser.beneficiary"})
 * @ApiFilter(SolidaryCandidateFilter::class, properties={"solidaryCandidate"})
 * @ApiFilter(DateFilter::class, properties={"createdDate": DateFilter::EXCLUDE_NULL,"lastActivityDate": DateFilter::EXCLUDE_NULL})
 * @ApiFilter(OrderFilter::class, properties={"id", "givenName", "status","familyName", "email", "gender", "nationality", "birthDate", "createdDate", "validatedDate", "lastActivityDate", "telephone"}, arguments={"orderParameterName"="order"})
 */
class User implements UserInterface, EquatableInterface
{
    const DEFAULT_ID = 999999999999;

    const MAX_DETOUR_DURATION = 600;
    const MAX_DETOUR_DISTANCE = 10000;

    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 2;
    const STATUS_ANONYMIZED = 3;

    const GENDER_FEMALE = 1;
    const GENDER_MALE = 2;
    const GENDER_OTHER = 3;

    const GENDERS = [
        self::GENDER_FEMALE,
        self::GENDER_MALE,
        self::GENDER_OTHER
    ];

    const PHONE_DISPLAY_RESTRICTED = 1;
    const PHONE_DISPLAY_ALL = 2;

    const AUTHORIZED_SIZES_DEFAULT_AVATAR = [
        "square_100",
        "square_250",
        "square_800"
    ];

    const PWD_SEND_TYPE_NONE = 0;    // password not sent
    const PWD_SEND_TYPE_SMS = 1;     // password sent by sms if phone present
    const PWD_SEND_TYPE_EMAIL = 2;   // password sent by email

    const MOBILE_APP_WEB = 1;
    const MOBILE_APP_IOS = 2;
    const MOBILE_APP_ANDROID = 3;

    const ROLE_DEFAULT = 3;  // Role we want to add by default when user register, ID is in auth_item (ROLE_USER_REGISTERED_FULL now)

    const SMOKE_NO = 0;
    const SMOKE_NOT_IN_CAR = 1;
    const SMOKE = 2;

    /**
     * @var int The id of this user.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"aRead","readUser","readCommunity","communities","readCommunityUser","results","threads", "thread","externalJourney","userStructure", "readSolidary","readPayment","carpoolExport","readReview"})
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var int User status (1 = active; 2 = disabled; 3 = anonymized).
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"aRead","aWrite","readUser","readCommunityUser","results","write"})
     */
    private $status;

    /**
     * @var string|null The first name of the user.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"aRead","aWrite","readUser","readCommunity","readCommunityUser","results","write", "threads", "thread","externalJourney", "readEvent", "massMigrate","communities", "readSolidary", "readAnimation", "readExport","readPublicProfile","readReview"})
     */
    private $givenName;

    /**
     * @var string|null The family name of the user.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"aRead","aWrite","readUser","write","communities", "readSolidary", "readAnimation", "readExport"})
     */
    private $familyName;

    /**
     * @var string|null The shorten family name of the user.
     *
     * @Groups({"aRead","readUser","results","write", "threads", "thread", "readCommunity", "readCommunityUser", "readEvent", "massMigrate", "readExport","readPublicProfile","readReview"})
     */
    private $shortFamilyName;

    /**
     * @var string|null The name of the user in a professional context.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readUser","write"})
     */
    private $proName;

    /**
     * @var string The email of the user.
     *
     * @Assert\NotBlank
     * @Assert\Email()
     * @ORM\Column(type="string", length=255, unique=true)
     * @Groups({"aRead","aWrite","readUser","write","checkValidationToken","passwordUpdateRequest","passwordUpdate", "readSolidary"})
     */
    private $email;

    /**
     * @var string|null The email of the user.
     * @Groups({"readUser", "write"})
     */
    private $oldEmail;

    /**
     * @var string The email of the user in a professional context.
     *
     * @Assert\Email()
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readUser","write"})
     */
    private $proEmail;

    /**
     * @var string The encoded password of the user.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readUser","write","passwordUpdate"})
     */
    private $password;

    /**
     * @var string The clear password of the user, used for delegation (not persisted !).
     *
     * @Groups({"massMigrate","write"})
     */
    private $clearPassword;

    /**
     * @var int|null If indirect registration, how we want to send the password to the user (0 = not sent, 1 = by sms, 2 = by email)
     *
     * @Groups("write")
     */
    private $passwordSendType;

    /**
     * @var int|null The gender of the user (1=female, 2=male, 3=nc)
     *
     * @ORM\Column(type="smallint")
     * @Groups({"aRead","aWrite","readUser","results","write","externalJourney"})
     */
    private $gender;

    /**
     * @var string|null The nationality of the user.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"aRead","aWrite","readUser","write"})
     */
    private $nationality;

    /**
     * @var \DateTimeInterface|null The birth date of the user.
     *
     * @ORM\Column(type="date", nullable=true)
     * @Groups({"aRead","aWrite","readUser","write"})
     *
     * @ApiProperty(
     *     attributes={
     *         "swagger_context"={"type"="string", "format"="date"}
     *     }
     * )
     */
    private $birthDate;

    /**
     * @var \DateTimeInterface|null The birth year of the user.
     *
     * @Groups({"readUser","results"})
     */
    private $birthYear;

    /**
     * @var string|null The telephone number of the user.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"aRead","aWrite","readUser","write","checkPhoneToken","results", "readSolidary"})
     */
    private $telephone;

    /**
     * @var string|null The telephone number of the user.
     * @Groups({"readUser", "write"})
     */
    private $oldTelephone;

    /**
     * @var int phone display configuration (1 = restricted (default); 2 = all).
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"aRead","aWrite","readUser","write","results"})
     */
    private $phoneDisplay;

    /**
     * @var int|null The maximum detour duration (in seconds) as a driver to accept a request proposal.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"readUser","write"})
     */
    private $maxDetourDuration;

    /**
     * @var int|null The maximum detour distance (in metres) as a driver to accept a request proposal.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"readUser","write"})
     */
    private $maxDetourDistance;

    /**
     * @var boolean|null The user accepts any route as a passenger from its origin to the destination.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readUser","write"})
     */
    private $anyRouteAsPassenger;

    /**
     * @var boolean|null The user accepts any transportation mode.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readUser","write"})
     */
    private $multiTransportMode;

    /**
     * @var int|null Smoking preferences.
     * 0 = i don't smoke
     * 1 = i don't smoke in car
     * 2 = i smoke
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"aRead","aWrite","readUser","write"})
     */
    private $smoke;

    /**
     * @var boolean|null Music preferences.
     * 0 = no music
     * 1 = i listen to music or radio
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readUser","write"})
     */
    private $music;

    /**
     * @var string|null Music favorites.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"aRead","aWrite","readUser","write"})
     */
    private $musicFavorites;

    /**
     * @var boolean|null Chat preferences.
     * 0 = no chat
     * 1 = chat
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readUser","write"})
     */
    private $chat;

    /**
     * @var string|null Chat favorite subjects.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"aRead","aWrite","readUser","write"})
     */
    private $chatFavorites;

    /**
     * @var boolean|null The user accepts to receive news about the platform.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readUser","write","readCommunity","readCommunityUser"})
     */
    private $newsSubscription;

    /**
     * @var \DateTimeInterface Creation date of the user.
     *
     * @ORM\Column(type="datetime")
     * @Groups({"aRead","readUser"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Validation date of the user.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readUser","write"})
     */
    private $validatedDate;

    /**
     * @var string|null Token for account validation by email
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readUser","write","checkValidationToken"})
     */
    private $emailToken;

    /**
     * @var \DateTimeInterface Updated date of the user.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("readUser")
     */
    private $updatedDate;

    /**
     * @var DateTime|null  Date of password token generation modification.
     *
     * @ORM\Column(type="datetime", length=255, nullable=true)
     * @Groups({"readUser","write"})
     */
    private $pwdTokenDate;

    /**
     * @var string|null Token for password modification.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readUser","write","passwordUpdateRequest","passwordUpdate"})
     */
    private $pwdToken;

    /**
     * @var string|null Token for phone validation.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readUser","write","checkPhoneToken"})
     */
    private $phoneToken;

    /**
     * @var \DateTimeInterface|null Validation date of the phone number.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readUser","write"})
     */
    private $phoneValidatedDate;

    /**
     * @var bool|null Mobile user
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readUser","write"})
     */
    private $mobile;

    /**
     * @var string User language
     * @Groups({"readUser","write"})
     * @ORM\Column(name="language", type="string", length=10, nullable=true)
     */
    private $language;

    /**
     * @var ArrayCollection|null A user may have many addresses.
     *
     * @ORM\OneToMany(targetEntity="\App\Geography\Entity\Address", mappedBy="user", cascade={"persist","remove"}, orphanRemoval=true)
     * @MaxDepth(1)
     * @ApiSubresource
     * @Groups({"readUser","write"})
     */
    private $addresses;

    /**
     * @var ArrayCollection|null A user may have many cars.
     *
     * @ORM\OneToMany(targetEntity="\App\User\Entity\Car", mappedBy="user", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"readUser","write"})
     */
    private $cars;

    /**
     * @var ArrayCollection|null A user may have many push token ids.
     *
     * @ORM\OneToMany(targetEntity="\App\User\Entity\PushToken", mappedBy="user", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"readUser","write"})
     */
    private $pushTokens;

    /**
     * @var ArrayCollection|null The proposals made for this user (in general by the user itself, except when it is a "posting for").
     *
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Proposal", mappedBy="user", cascade={"remove"}, orphanRemoval=true)
     * @MaxDepth(1)
     * @Groups({"proposals", "get"})
     * @Apisubresource
     */
    private $proposals;

    /**
     * @var ArrayCollection|null The proposals made by this user for another user.
     *
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Proposal", mappedBy="userDelegate", cascade={"remove"}, orphanRemoval=true)
     * @MaxDepth(1)
     * @Apisubresource
     */
    private $proposalsDelegate;

    /**
     * @var ArrayCollection|null The asks made by this user.
     *
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Ask", mappedBy="user", cascade={"remove"}, orphanRemoval=true)
     */
    private $asks;

    /**
     * @var ArrayCollection|null The events made by this user.
     *
     * @ORM\OneToMany(targetEntity="\App\Event\Entity\Event", mappedBy="user", cascade={"remove"}, orphanRemoval=true)
     */
    private $events;

    /**
    * @var ArrayCollection|null The communityUser associated to this user
    *
    * @ORM\OneToMany(targetEntity="\App\Community\Entity\CommunityUser", mappedBy="user", cascade={"remove"}, orphanRemoval=true)
    */
    private $communityUsers;

    /**
    * @var int|null Community choose by a user
    * @Groups({"readUser","write"})
    */
    private $communityId;

    /**
     * @var ArrayCollection|null The asks made for this user.
     *
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Ask", mappedBy="userRelated", cascade={"remove"}, orphanRemoval=true)
     */
    private $asksRelated;

    /**
     * @var ArrayCollection|null The asks made by this user (in general by the user itself, except when it is a "posting for").
     *
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Ask", mappedBy="userDelegate", cascade={"remove"}, orphanRemoval=true)
     */
    private $asksDelegate;

    /**
     * @var ArrayCollection|null The images of the user.
     *
     * @ORM\OneToMany(targetEntity="\App\Image\Entity\Image", mappedBy="user", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"readUser","results","write"})
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $images;

    /**
     * @var ArrayCollection|null A user may have many auth assignments.
     *
     * @ORM\OneToMany(targetEntity="\App\Auth\Entity\UserAuthAssignment", mappedBy="user", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"readUser","write"})
     * @MaxDepth(1)
     */
    private $userAuthAssignments;

    /**
     * @Groups({"readUser"})
     * @MaxDepth(1)
     */
    private $roles;

    /**
     * @var ArrayCollection|null The mass import files of the user.
     *
     * @ORM\OneToMany(targetEntity="\App\Match\Entity\Mass", mappedBy="user", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"mass"})
     * @MaxDepth(1)
     * @ApiSubresource
     */
    private $masses;

    /**
     * @var ArrayCollection|null The messages sent by the user.
     *
     * @ORM\OneToMany(targetEntity="\App\Communication\Entity\Message", mappedBy="user", cascade={"persist","remove"}, orphanRemoval=true)
     * @MaxDepth(1)
     * @ApiSubresource
     */
    private $messages;

    /**
     * @var ArrayCollection|null The messages received by the user.
     *
     * @ORM\OneToMany(targetEntity="\App\Communication\Entity\Recipient", mappedBy="user", cascade={"persist","remove"}, orphanRemoval=true)
     * @MaxDepth(1)
     * ApiSubresource
     */
    private $recipients;

    /**
     * @var ArrayCollection|null The notifications sent to the user.
     *
     * @ORM\OneToMany(targetEntity="\App\Communication\Entity\Notified", mappedBy="user", cascade={"persist","remove"}, orphanRemoval=true)
     */
    private $notifieds;

    /**
     * @var ArrayCollection|null A user may have many action logs.
     *
     * @ORM\OneToMany(targetEntity="\App\Action\Entity\Log", mappedBy="user", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"readUser","write"})
     */
    private $logs;

    /**
     * @var ArrayCollection|null A user may have many action logs as an delegate.
     *
     * @ORM\OneToMany(targetEntity="\App\Action\Entity\Log", mappedBy="userDelegate", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"readUser","write"})
     */
    private $logsAsDelegate;

    /**
     * @var ArrayCollection|null A user may have many action logs.
     *
     * @ORM\OneToMany(targetEntity="\App\Action\Entity\Diary", mappedBy="user", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"readUser","write"})
     */
    private $diaries;

    /**
     * @var ArrayCollection|null A user may have many diary action logs.
     *
     * @ORM\OneToMany(targetEntity="\App\Action\Entity\Diary", mappedBy="author", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"write"})
     */
    private $diariesAuthor;

    /**
    * @var ArrayCollection|null A user may have many user notification preferences.
    *
    * @ORM\OneToMany(targetEntity="\App\User\Entity\UserNotification", mappedBy="user", cascade={"persist","remove"}, orphanRemoval=true)
    */
    private $userNotifications;

    /**
     * @var ArrayCollection|null The campaigns made by this user.
     *
     * @ORM\OneToMany(targetEntity="\App\MassCommunication\Entity\Campaign", mappedBy="user", cascade={"remove"}, orphanRemoval=true)
     */
    private $campaigns;

    /**
     * @var ArrayCollection|null The campaing deliveries where this user is recipient.
     *
     * @ORM\OneToMany(targetEntity="\App\MassCommunication\Entity\Delivery", mappedBy="user", cascade={"remove"}, orphanRemoval=true)
     */
    private $deliveries;

    /**
     * @var UserImport|null The user import data.
     *
     * @ORM\OneToOne(targetEntity="\App\Import\Entity\UserImport", mappedBy="user", cascade={"remove"})
     * @Groups({"readUser"})
     * @MaxDepth(1)
     */
    private $import;

    /**
     * @var array|null The avatars of the user
     * @Groups({"readUser","readCommunity","results","threads","thread","externalJourney", "readSolidary", "readAnimation"})
     */
    private $avatars;

    /**
     * @var string|null Default avatar of the user
     * @Groups({"aRead","readUser","readPublicProfile","readReview"})
     */
    private $avatar;

    /**
     * @var array|null The threads of the user
     * @Groups("threads")
     */
    private $threads;

    /**
     * @var array|null The permissions granted
     * @Groups({"permissions"})
     */
    private $permissions;

    /**
     * @var array|null The user alerts preferences
     * @Groups("alerts")
     */
    private $alerts;

    /**
     * @var string|null Facebook ID of the user
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readUser","write"})
     */
    private $facebookId;

    /**
     * @var string|null External ID of the user for a SSO connection
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ssoId;

    /**
     * @var string|null External Provider for a SSO connection
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ssoProvider;

    /**
     * @var User|null Admin that create the user.
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User")
     * @Groups({"readUser","write"})
     * @MaxDepth(1)
     */
    private $userDelegate;

    /**
     * @var App|null App that create the user.
     *
     * @ORM\ManyToOne(targetEntity="\App\App\Entity\App")
     * @Groups({"readUser","write"})
     * @MaxDepth(1)
     */
    private $appDelegate;

    /**
     * @var ArrayCollection|null The carpool proofs of the user as a driver.
     *
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\CarpoolProof", mappedBy="driver")
     */
    private $carpoolProofsAsDriver;

    /**
     * @var ArrayCollection|null The carpool proofs of the user as a driver.
     *
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\CarpoolProof", mappedBy="passenger")
     */
    private $carpoolProofsAsPassenger;

    /**
     * @var string|null Token for news unsubscription
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readUser","write"})
     */
    private $unsubscribeToken;

    /**
     * @var \DateTimeInterface Date when user unsubscribe from email
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("readUser")
     */
    private $unsubscribeDate;

    /**
     * @var string|null the unsubscribe message we return to client : change this later By listener
     * @Groups({"readUser"})
     */
    private $unsubscribeMessage;

    /**
     * @var bool|null used to indicate a attempt to import this already registered user.
     * @Groups({"massMigrate"})
     */
    private $alreadyRegistered;

    /**
     * @var int|null Registration from mobile (web app:1, iOS:2, Android:3)
     *
     * @Groups({"readUser","write","passwordUpdateRequest"})
     */
    private $mobileRegistration;

    /**
     * @var string|null The link used to validate the email (useful for mobile apps)
     * @Groups({"readUser","write","passwordUpdateRequest"})
     */
    private $backLink;

    /**
     * @var \DateTimeInterface Last user activity date
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"aRead","readUser","write"})
     */
    private $lastActivityDate;

    /**
     * @var SolidaryUser|null The SolidaryUser possibly linked to this User
     * @ORM\OneToOne(targetEntity="\App\Solidary\Entity\SolidaryUser", inversedBy="user", cascade={"persist","remove"})
     * @Groups({"readUser","write","writeSolidary"})
     * @MaxDepth(1)
     */
    private $solidaryUser;

    /**
     * @var array|null used to get the solidaries of a user
     * @Groups({"readSolidary"})
     * @MaxDepth(1)
     */
    private $solidaries;

    /**
     * @var array|null Get User Solidary Structures
     * @Groups({"readUser", "write"})
     * @MaxDepth(1)
     */
    private $solidaryStructures;

    /**
     * @var CommunityUser|null The communityUser link to the user, use in admin for get the record CommunityUser from the User ressource
     * @Groups({"readUserAdmin" })
     */
    private $adminCommunityUser;

    /**
     * @var MassPerson|null The Mass person related to the suer if the user is imported from a Mass migration
     *
     * @ORM\OneToOne(targetEntity="\App\Match\Entity\MassPerson", mappedBy="user")
     * @MaxDepth(1)
     * @Groups({"readUser"})
     */
    private $massPerson;

    /**
     * @var ArrayCollection|null A User can have multiple entry in Operate
     *
     * @ORM\OneToMany(targetEntity="\App\Solidary\Entity\Operate", mappedBy="user", cascade={"persist","remove"})
     * @Groups({"readUser", "write"})
     * @MaxDepth(1)
     */
    private $operates;

    /**
     * @var int|null PaymentProfileId of a User
     *
     * @Groups({"readPayment"})
     * @MaxDepth(1)
     */
    private $paymentProfileId;
    
    /**
     * @var array|null BankAccounts of a User
     *
     * @Groups({"readPayment"})
     * @MaxDepth(1)
     */
    private $bankAccounts;

    /**
     * @var array|null Wallets of a User
     *
     * @Groups({"readPayment"})
     * @MaxDepth(1)
     */
    private $wallets;

    /**
    * @var string|null CarpoolExport of a User
    *
    * @Groups({"carpoolExport"})
    * @MaxDepth(1)
    */
    private $carpoolExport;

    /**
     * @var bool|null If the User can receive a review from the current User (used in Carpool Results)
     *
    * @Groups({"results"})
     */
    private $canReceiveReview;

    /**
     * @var bool|null If the Reviews are enable on this instance
     * @Groups({"readUser", "readReview"})
     */
    private $userReviewsActive;

    /**
     * @var bool|null If the User is an experienced carpooler
     * @Groups({"readUser","results","write", "threads", "thread", "readCommunity", "readCommunityUser", "readEvent", "massMigrate", "readExport","readPublicProfile","readReview"})
     */
    private $experienced;

    /**
     * @var int|null Number of unread carpool messages
     * @Groups({"readUser"})
     */
    private $unreadCarpoolMessageNumber;

    /**
     * @var int|null Number of unread direct messages
     * @Groups({"readUser"})
     */
    private $unreadDirectMessageNumber;

    /**
     * @var int|null Number of unread solidary messages
     * @Groups({"readUser"})
     */
    private $unreadSolidaryMessageNumber;

    /**
     * @var int|null The savedCo2 of this user in grams
     * @Groups({"readUser","results","write", "threads", "thread", "readCommunity", "readCommunityUser", "readEvent", "massMigrate", "readExport","readPublicProfile","readReview"})
     */
    private $savedCo2;

    // ADMIN

    /**
     * @var string|null The user main image
     * @Groups({"aRead","aWrite"})
     */
    private $image;

    /**
     * @var Address The user home address
     * @Groups({"aRead","aWrite"})
     */
    private $homeAddress;

    /**
     * @var array|null The user roles
     * @Groups({"aRead","aWrite"})
     */
    private $rolesTerritory;

    public function __construct($status = null)
    {
        $this->id = self::DEFAULT_ID;
        $this->addresses = new ArrayCollection();
        $this->cars = new ArrayCollection();
        $this->proposals = new ArrayCollection();
        $this->proposalsDelegate = new ArrayCollection();
        $this->asks = new ArrayCollection();
        $this->asksRelated = new ArrayCollection();
        $this->asksDelegate = new ArrayCollection();
        $this->userAuthAssignments = new ArrayCollection();
        $this->masses = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->recipients = new ArrayCollection();
        $this->notifieds = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->logsAsDelegate = new ArrayCollection();
        $this->diaries = new ArrayCollection();
        $this->diariesAdmin = new ArrayCollection();
        $this->userNotifications = new ArrayCollection();
        $this->campaigns = new ArrayCollection();
        $this->deliveries = new ArrayCollection();
        $this->carpoolProofsAsDriver = new ArrayCollection();
        $this->carpoolProofsAsPassenger = new ArrayCollection();
        $this->pushTokens = new ArrayCollection();
        $this->operates = new ArrayCollection();
        $this->communityUsers = new ArrayCollection();
        $this->solidaryStructures = [];
        $this->roles = [];
        $this->rolesTerritory = [];
        $this->bankAccounts = [];
        $this->wallets = [];
        if (is_null($status)) {
            $status = self::STATUS_ACTIVE;
        }
        $this->setStatus($status);
        $this->setAlreadyRegistered(false);
        $this->setMobileRegistration(null);
        $this->setExperienced(false);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function setGivenName(?string $givenName): self
    {
        $this->givenName = $givenName;

        return $this;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function setFamilyName(?string $familyName): self
    {
        $this->familyName = $familyName;

        return $this;
    }

    public function getShortFamilyName(): ?string
    {
        if (is_null($this->familyName) || $this->familyName==="" || !isset($this->familyName[0])) {
            return ".";
        }

        $familyName=utf8_decode($this->familyName);
        $familyName=strtoupper($familyName[0]). ".";
        $familyName=utf8_encode($familyName);
        return $familyName;
    }

    public function getProName(): ?string
    {
        return $this->proName;
    }

    public function setProName(?string $proName): self
    {
        $this->proName = $proName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getOldEmail(): ?string
    {
        return $this->oldEmail;
    }

    public function setOldEmail(?string $oldEmail): self
    {
        $this->oldEmail = $oldEmail;

        return $this;
    }

    public function getProEmail(): ?string
    {
        return $this->proEmail;
    }

    public function setProEmail(?string $proEmail): self
    {
        $this->proEmail = $proEmail;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getClearPassword(): ?string
    {
        return $this->clearPassword;
    }

    public function setClearPassword(?string $clearPassword): self
    {
        $this->clearPassword = $clearPassword;

        return $this;
    }

    public function getPasswordSendType(): ?int
    {
        return $this->passwordSendType;
    }

    public function setPasswordSendType(?int $passwordSendType): self
    {
        $this->passwordSendType = $passwordSendType;

        return $this;
    }

    public function getGender()
    {
        return $this->gender;
    }

    public function setGender($gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function setNationality(?string $nationality): self
    {
        $this->nationality = $nationality;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getBirthYear(): ?int
    {
        return ($this->birthDate ? $this->birthDate->format('Y') : null);
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getPhoneDisplay(): ?int
    {
        return $this->phoneDisplay;
    }

    public function setPhoneDisplay(?int $phoneDisplay): self
    {
        $this->phoneDisplay = $phoneDisplay;

        return $this;
    }

    public function getOldTelephone(): ?string
    {
        return $this->oldTelephone;
    }

    public function setOldTelephone(?string $oldTelephone): self
    {
        $this->oldTelephone = $oldTelephone;

        return $this;
    }

    public function getMaxDetourDuration(): ?int
    {
        return (!is_null($this->maxDetourDuration) ? $this->maxDetourDuration : self::MAX_DETOUR_DURATION);
    }

    public function setMaxDetourDuration(?int $maxDetourDuration): self
    {
        $this->maxDetourDuration = $maxDetourDuration;

        return $this;
    }

    public function getMaxDetourDistance(): ?int
    {
        return (!is_null($this->maxDetourDistance) ? $this->maxDetourDistance : self::MAX_DETOUR_DISTANCE);
    }

    public function setMaxDetourDistance(?int $maxDetourDistance): self
    {
        $this->maxDetourDistance = $maxDetourDistance;

        return $this;
    }

    public function getAnyRouteAsPassenger(): ?bool
    {
        return $this->anyRouteAsPassenger;
    }

    public function setAnyRouteAsPassenger(?bool $anyRouteAsPassenger): self
    {
        $this->anyRouteAsPassenger = $anyRouteAsPassenger;

        return $this;
    }

    public function getMultiTransportMode(): ?bool
    {
        return $this->multiTransportMode;
    }

    public function setMultiTransportMode(?bool $multiTransportMode): self
    {
        $this->multiTransportMode = $multiTransportMode;

        return $this;
    }

    public function getSmoke(): ?int
    {
        return $this->smoke;
    }

    public function setSmoke(?int $smoke): self
    {
        $this->smoke = $smoke;

        return $this;
    }

    public function hasMusic(): ?bool
    {
        return $this->music;
    }

    public function setMusic(?bool $music): self
    {
        $this->music = $music;

        return $this;
    }

    public function getMusicFavorites(): ?string
    {
        return $this->musicFavorites;
    }

    public function setMusicFavorites(?string $musicFavorites): self
    {
        $this->musicFavorites = $musicFavorites;

        return $this;
    }

    public function hasChat(): ?bool
    {
        return $this->chat;
    }

    public function setChat(?bool $chat): self
    {
        $this->chat = $chat;

        return $this;
    }

    public function getChatFavorites(): ?string
    {
        return $this->chatFavorites;
    }

    public function setChatFavorites(?string $chatFavorites): self
    {
        $this->chatFavorites = $chatFavorites;

        return $this;
    }

    public function hasNewsSubscription(): ?bool
    {
        return $this->newsSubscription;
    }

    public function setNewsSubscription(?bool $newsSubscription): self
    {
        $this->newsSubscription = $newsSubscription;

        return $this;
    }

    public function getPwdToken(): ?string
    {
        return $this->pwdToken;
    }

    public function setPwdToken(?string $pwdToken): self
    {
        $this->pwdToken = $pwdToken;
        $this->setPwdTokenDate($pwdToken ? new \DateTime() : null);
        return $this;
    }

    public function getPwdTokenDate(): ?\DateTimeInterface
    {
        return $this->pwdTokenDate;
    }

    public function setPwdTokenDate(?DateTime $pwdTokenDate): self
    {
        $this->pwdTokenDate = $pwdTokenDate;
        return $this;
    }

    public function getPhoneToken(): ?string
    {
        return $this->phoneToken;
    }

    public function setPhoneToken(?string $phoneToken): self
    {
        $this->phoneToken = $phoneToken;
        return $this;
    }

    public function getPhoneValidatedDate(): ?\DateTimeInterface
    {
        return $this->phoneValidatedDate;
    }

    public function setPhoneValidatedDate(?\DateTimeInterface $phoneValidatedDate): ?self
    {
        $this->phoneValidatedDate = $phoneValidatedDate;

        return $this;
    }

    public function hasMobile(): ?bool
    {
        return $this->mobile ? true : false;
    }

    public function setMobile(?bool $mobile): self
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): self
    {
        $this->language= $language;
        return $this;
    }

    public function getAddresses()
    {
        return $this->addresses->getValues();
    }

    public function addAddress(Address $address): self
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses->add($address);
            $address->setUser($this);
        }

        return $this;
    }

    public function removeAddress(Address $address): self
    {
        if ($this->addresses->contains($address)) {
            $this->addresses->removeElement($address);
            // set the owning side to null (unless already changed)
            if ($address->getUser() === $this) {
                $address->setUser(null);
            }
        }

        return $this;
    }

    public function getImages()
    {
        return $this->images->getValues();
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setUser($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getUser() === $this) {
                $image->setUser(null);
            }
        }

        return $this;
    }

    public function getCars()
    {
        return $this->cars->getValues();
    }

    public function addCar(Car $car): self
    {
        if (!$this->cars->contains($car)) {
            $this->cars->add($car);
            $car->setUser($this);
        }

        return $this;
    }

    public function removeCar(Car $car): self
    {
        if ($this->cars->contains($car)) {
            $this->cars->removeElement($car);
            // set the owning side to null (unless already changed)
            if ($car->getUser() === $this) {
                $car->setUser(null);
            }
        }

        return $this;
    }

    public function getPushTokens()
    {
        return $this->pushTokens->getValues();
    }

    public function addPushToken(PushToken $pushToken): self
    {
        if (!$this->pushTokens->contains($pushToken)) {
            $this->pushTokens->add($pushToken);
            $pushToken->setUser($this);
        }

        return $this;
    }

    public function removePushToken(PushToken $pushToken): self
    {
        if ($this->pushTokens->contains($pushToken)) {
            $this->pushTokens->removeElement($pushToken);
            // set the owning side to null (unless already changed)
            if ($pushToken->getUser() === $this) {
                $pushToken->setUser(null);
            }
        }

        return $this;
    }

    public function getProposals()
    {
        return $this->proposals->getValues();
    }

    public function addProposal(Proposal $proposal): self
    {
        if (!$this->proposals->contains($proposal)) {
            $this->proposals->add($proposal);
            $proposal->setUser($this);
        }

        return $this;
    }

    public function removeProposal(Proposal $proposal): self
    {
        if ($this->proposals->contains($proposal)) {
            $this->proposals->removeElement($proposal);
            // set the owning side to null (unless already changed)
            if ($proposal->getUser() === $this) {
                $proposal->setUser(null);
            }
        }

        return $this;
    }

    public function getProposalsDelegate()
    {
        return $this->proposalsDelegate->getValues();
    }

    public function addProposalDelegate(Proposal $proposalDelegate): self
    {
        if (!$this->proposalsDelegate->contains($proposalDelegate)) {
            $this->proposalsDelegate->add($proposalDelegate);
            $proposalDelegate->setUserDelegate($this);
        }

        return $this;
    }

    public function removeProposalDelegate(Proposal $proposalDelegate): self
    {
        if ($this->proposalsDelegate->contains($proposalDelegate)) {
            $this->proposalsDelegate->removeElement($proposalDelegate);
            // set the owning side to null (unless already changed)
            if ($proposalDelegate->getUserDelegate() === $this) {
                $proposalDelegate->setUserDelegate(null);
            }
        }

        return $this;
    }

    public function getAsks()
    {
        return $this->asks->getValues();
    }

    public function addAsk(Ask $ask): self
    {
        if (!$this->asks->contains($ask)) {
            $this->asks->add($ask);
            $ask->setUser($this);
        }

        return $this;
    }

    public function removeAsk(Ask $ask): self
    {
        if ($this->asks->contains($ask)) {
            $this->asks->removeElement($ask);
            // set the owning side to null (unless already changed)
            if ($ask->getUser() === $this) {
                $ask->setUser(null);
            }
        }

        return $this;
    }



    public function getAsksRelated()
    {
        return $this->asksRelated->getValues();
    }

    public function addAsksRelated(Ask $asksRelated): self
    {
        if (!$this->asksRelated->contains($asksRelated)) {
            $this->asksRelated->add($asksRelated);
            $asksRelated->setUser($this);
        }

        return $this;
    }

    public function removeAsksRelated(Ask $asksRelated): self
    {
        if ($this->asksRelated->contains($asksRelated)) {
            $this->asksRelated->removeElement($asksRelated);
            // set the owning side to null (unless already changed)
            if ($asksRelated->getUser() === $this) {
                $asksRelated->setUser(null);
            }
        }

        return $this;
    }

    public function getAsksDelegate()
    {
        return $this->asksDelegate->getValues();
    }

    public function addAskDelegate(Ask $askDelegate): self
    {
        if (!$this->asksDelegate->contains($askDelegate)) {
            $this->asksDelegate->add($askDelegate);
            $askDelegate->setUserDelegate($this);
        }

        return $this;
    }

    public function removeAskDelegate(Ask $askDelegate): self
    {
        if ($this->asksDelegate->contains($askDelegate)) {
            $this->asksDelegate->removeElement($askDelegate);
            // set the owning side to null (unless already changed)
            if ($askDelegate->getUserDelegate() === $this) {
                $askDelegate->setUserDelegate(null);
            }
        }

        return $this;
    }

    public function getEvents()
    {
        return $this->events->getValues();
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setUser($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
            // set the owning side to null (unless already changed)
            if ($event->getUser() === $this) {
                $event->setUser(null);
            }
        }

        return $this;
    }

    public function getCommunityUsers()
    {
        return $this->communityUsers->getValues();
    }

    public function addCommunityUser(CommunityUser $communityUser): self
    {
        if (!$this->events->contains($communityUser)) {
            $this->events->add($communityUser);
            $communityUser->setUser($this);
        }

        return $this;
    }

    public function removeCommunityUser(CommunityUser $communityUser): self
    {
        if ($this->events->contains($communityUser)) {
            $this->events->removeElement($communityUser);
            // set the owning side to null (unless already changed)
            if ($communityUser->getUser() === $this) {
                $communityUser->setUser(null);
            }
        }

        return $this;
    }

    public function getCommunityId(): ?int
    {
        return $this->communityId;
    }

    public function setCommunityId($communityId)
    {
        $this->communityId = $communityId;
    }

    public function getUserAuthAssignments()
    {
        return $this->userAuthAssignments->getValues();
    }

    public function addUserAuthAssignment(UserAuthAssignment $userAuthAssignment): self
    {
        if (!$this->userAuthAssignments->contains($userAuthAssignment)) {
            $this->userAuthAssignments->add($userAuthAssignment);
            $userAuthAssignment->setUser($this);
        }

        return $this;
    }

    public function removeUserAuthAssignment(UserAuthAssignment $userAuthAssignment): self
    {
        // This contains... does'nt seem to work
        if ($this->userAuthAssignments->contains($userAuthAssignment)) {
            $this->userAuthAssignments->removeElement($userAuthAssignment);
            // set the owning side to null (unless already changed)
            if ($userAuthAssignment->getUser() === $this) {
                $userAuthAssignment->setUser(null);
            }
        }

        return $this;
    }

    public function removeUserAuthAssignments()
    {
        foreach ($this->userAuthAssignments as $userAuthAssignment) {
            $this->userAuthAssignments->removeElement($userAuthAssignment);
            if ($userAuthAssignment->getUser() === $this) {
                $userAuthAssignment->setUser(null);
            }
        }
    }

    public function getMasses()
    {
        return $this->masses->getValues();
    }

    public function addMass(Mass $mass): self
    {
        if (!$this->masses->contains($mass)) {
            $this->masses->add($mass);
            $mass->setUser($this);
        }

        return $this;
    }

    public function removeMass(Mass $mass): self
    {
        if ($this->masses->contains($mass)) {
            $this->masses->removeElement($mass);
            // set the owning side to null (unless already changed)
            if ($mass->getUser() === $this) {
                $mass->setUser(null);
            }
        }

        return $this;
    }

    public function getMessages()
    {
        return $this->messages->getValues();
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setUser($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getUser() === $this) {
                $message->setUser(null);
            }
        }

        return $this;
    }

    public function getRecipients()
    {
        return $this->recipients->getValues();
    }

    public function addRecipient(Recipient $recipient): self
    {
        if (!$this->recipients->contains($recipient)) {
            $this->recipients[] = $recipient;
            $recipient->setUser($this);
        }

        return $this;
    }

    public function removeRecipient(Recipient $recipient): self
    {
        if ($this->recipients->contains($recipient)) {
            $this->recipients->removeElement($recipient);
            // set the owning side to null (unless already changed)
            if ($recipient->getUser() === $this) {
                $recipient->setUser(null);
            }
        }

        return $this;
    }

    public function getNotifieds()
    {
        return $this->notifieds->getValues();
    }

    public function addNotified(Notified $notified): self
    {
        if (!$this->notifieds->contains($notified)) {
            $this->notifieds[] = $notified;
            $notified->setUser($this);
        }

        return $this;
    }

    public function removeNotified(Notified $notified): self
    {
        if ($this->notifieds->contains($notified)) {
            $this->notifieds->removeElement($notified);
            // set the owning side to null (unless already changed)
            if ($notified->getUser() === $this) {
                $notified->setUser(null);
            }
        }

        return $this;
    }

    public function getLogs()
    {
        return $this->logs->getValues();
    }

    public function addLog(Log $log): self
    {
        if (!$this->logs->contains($log)) {
            $this->logs->add($log);
            $log->setUser($this);
        }

        return $this;
    }

    public function removeLog(Log $log): self
    {
        if ($this->logs->contains($log)) {
            $this->logs->removeElement($log);
            // set the owning side to null (unless already changed)
            if ($log->getUser() === $this) {
                $log->setUser(null);
            }
        }

        return $this;
    }

    public function getLogsAsDelegate()
    {
        return $this->logsAsDelegate->getValues();
    }

    public function addLogAsDelegate(Log $logAsDelegate): self
    {
        if (!$this->logsAsDelegate->contains($logAsDelegate)) {
            $this->logsAsDelegate->add($logAsDelegate);
            $logAsDelegate->setUserDelegate($this);
        }

        return $this;
    }

    public function removeLogAsDelegate(Log $logAsDelegate): self
    {
        if ($this->logsAsDelegate->contains($logAsDelegate)) {
            $this->logsAsDelegate->removeElement($logAsDelegate);
            // set the owning side to null (unless already changed)
            if ($logAsDelegate->getUserDelegate() === $this) {
                $logAsDelegate->setUserDelegate(null);
            }
        }

        return $this;
    }

    public function getDiaries()
    {
        return $this->diaries->getValues();
    }

    public function addDiary(Diary $diary): self
    {
        if (!$this->diaries->contains($diary)) {
            $this->diaries->add($diary);
            $diary->setUser($this);
        }

        return $this;
    }

    public function removeDiary(Diary $diary): self
    {
        if ($this->diaries->contains($diary)) {
            $this->diaries->removeElement($diary);
            // set the owning side to null (unless already changed)
            if ($diary->getUser() === $this) {
                $diary->setUser(null);
            }
        }

        return $this;
    }

    public function getDiariesAuthor()
    {
        return $this->diariesAuthor->getValues();
    }

    public function addDiaryAuthor(Diary $diaryAuthor): self
    {
        if (!$this->diariesAuthor->contains($diaryAuthor)) {
            $this->diariesAuthor->add($diaryAuthor);
            $diaryAuthor->setAuthor($this);
        }

        return $this;
    }

    public function removeDiaryAuthor(Diary $diaryAdmin): self
    {
        if ($this->diariesAuthor->contains($diaryAdmin)) {
            $this->diariesAuthor->removeElement($diaryAdmin);
            // set the owning side to null (unless already changed)
            if ($diaryAdmin->getAuthor() === $this) {
                $diaryAdmin->setAuthor(null);
            }
        }

        return $this;
    }

    public function getUserNotifications()
    {
        return $this->userNotifications->getValues();
    }

    public function addUserNotification(UserNotification $userNotification): self
    {
        if (!$this->userNotifications->contains($userNotification)) {
            $this->userNotifications->add($userNotification);
            $userNotification->setUser($this);
        }

        return $this;
    }

    public function removeUserNotification(UserNotification $userNotification): self
    {
        if ($this->userNotifications->contains($userNotification)) {
            $this->userNotifications->removeElement($userNotification);
            // set the owning side to null (unless already changed)
            if ($userNotification->getUser() === $this) {
                $userNotification->setUser(null);
            }
        }

        return $this;
    }

    public function getCampaigns()
    {
        return $this->campaigns->getValues();
    }

    public function addCampaign(Campaign $campaign): self
    {
        if (!$this->campaigns->contains($campaign)) {
            $this->campaigns->add($campaign);
            $campaign->setUser($this);
        }

        return $this;
    }

    public function removeCampaign(Campaign $campaign): self
    {
        if ($this->campaigns->contains($campaign)) {
            $this->campaigns->removeElement($campaign);
            // set the owning side to null (unless already changed)
            if ($campaign->getUser() === $this) {
                $campaign->setUser(null);
            }
        }

        return $this;
    }

    public function getDeliveries()
    {
        return $this->deliveries->getValues();
    }

    public function addDelivery(Delivery $delivery): self
    {
        if (!$this->deliveries->contains($delivery)) {
            $this->deliveries->add($delivery);
            $delivery->setUser($this);
        }

        return $this;
    }

    public function removeDelivery(Delivery $delivery): self
    {
        if ($this->deliveries->contains($delivery)) {
            $this->deliveries->removeElement($delivery);
            // set the owning side to null (unless already changed)
            if ($delivery->getUser() === $this) {
                $delivery->setUser(null);
            }
        }

        return $this;
    }

    public function getCarpoolProofsAsDriver()
    {
        return $this->carpoolProofsAsDriver->getValues();
    }

    public function addCarpoolProofsAsDriver(CarpoolProof $carpoolProofAsDriver): self
    {
        if (!$this->carpoolProofsAsDriver->contains($carpoolProofAsDriver)) {
            $this->carpoolProofsAsDriver->add($carpoolProofAsDriver);
            $carpoolProofAsDriver->setDriver($this);
        }

        return $this;
    }

    public function removeCarpoolProofsAsDriver(CarpoolProof $carpoolProofAsDriver): self
    {
        if ($this->carpoolProofsAsDriver->contains($carpoolProofAsDriver)) {
            $this->carpoolProofsAsDriver->removeElement($carpoolProofAsDriver);
            // set the owning side to null (unless already changed)
            if ($carpoolProofAsDriver->getDriver() === $this) {
                $carpoolProofAsDriver->setDriver(null);
            }
        }

        return $this;
    }

    public function getCarpoolProofsAsPassenger()
    {
        return $this->carpoolProofsAsPassenger->getValues();
    }

    public function addCarpoolProofsAsPassenger(CarpoolProof $carpoolProofAsPassenger): self
    {
        if (!$this->carpoolProofsAsPassenger->contains($carpoolProofAsPassenger)) {
            $this->carpoolProofsAsPassenger->add($carpoolProofAsPassenger);
            $carpoolProofAsPassenger->setPassenger($this);
        }

        return $this;
    }

    public function removeCarpoolProofsAsPassenger(CarpoolProof $carpoolProofAsPassenger): self
    {
        if ($this->carpoolProofsAsPassenger->contains($carpoolProofAsPassenger)) {
            $this->carpoolProofsAsPassenger->removeElement($carpoolProofAsPassenger);
            // set the owning side to null (unless already changed)
            if ($carpoolProofAsPassenger->getPassenger() === $this) {
                $carpoolProofAsPassenger->setPassenger(null);
            }
        }

        return $this;
    }

    public function getImport(): ?UserImport
    {
        return $this->import;
    }

    public function setImport(?UserImport $import): self
    {
        $this->import = $import;

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getUpdatedDate(): ?\DateTimeInterface
    {
        return $this->updatedDate;
    }

    public function setUpdatedDate(\DateTimeInterface $updatedDate): self
    {
        $this->updatedDate = $updatedDate;

        return $this;
    }

    public function getValidatedDate(): ?\DateTimeInterface
    {
        return $this->validatedDate;
    }

    public function setValidatedDate(?\DateTimeInterface $validatedDate): self
    {
        $this->validatedDate = $validatedDate;

        return $this;
    }

    public function getEmailToken(): ?string
    {
        return $this->emailToken;
    }

    public function setEmailToken(?string $emailToken): self
    {
        $this->emailToken = $emailToken;
        return $this;
    }

    public function getRoles(): array
    {
        // we return an array of ROLE_***
        foreach ($this->userAuthAssignments as $userAuthAssignment) {
            if ($userAuthAssignment->getAuthItem()->getType() == AuthItem::TYPE_ROLE) {
                $this->roles[] = $userAuthAssignment->getAuthItem()->getName();
            }
        }
        //Security : if an user has no roles but it shouldn't be possible
        return $this->roles ? array_unique($this->roles) : array(AuthItem::ROLE_USER_REGISTERED_FULL);
    }

    public function getSalt()
    {
        return  null;
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function eraseCredentials()
    {
    }

    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof User) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->email !== $user->getUsername()) {
            return false;
        }

        return true;
    }

    public function getPermissions(): ?array
    {
        return $this->permissions;
    }

    public function setPermissions(array $permissions): self
    {
        $this->permissions = $permissions;

        return $this;
    }

    public function getAlerts(): ?array
    {
        return $this->alerts;
    }

    public function setAlerts(?array $alerts): self
    {
        $this->alerts = $alerts;

        return $this;
    }

    public function getThreads(): ?array
    {
        return $this->threads;
    }

    public function setThreads(array $threads): self
    {
        $this->threads = $threads;

        return $this;
    }

    public function getAvatars(): ?array
    {
        return $this->avatars;
    }

    public function setAvatars(?array $avatars): self
    {
        $this->avatars = $avatars;

        return $this;
    }

    public function addAvatar(string $avatar): ?array
    {
        if (is_null($this->avatars)) {
            $this->avatars = [];
        }
        if (!in_array($avatar, $this->avatars)) {
            $this->avatars[]=$avatar;
        }
        return $this->avatars;
    }

    public function removeAvatar(string $avatar): ?array
    {
        if ($key = array_search($avatar, $this->avatars)) {
            unset($this->avatars[$key]);
        }
        return $this->avatars;
    }

    public function getAvatar(): ?string
    {
        // By default, return the last avatar
        $avatar = "";
        if (is_array($this->getAvatars()) && count($this->getAvatars())>0) {
            return $this->getAvatars()[count($this->getAvatars())-1];
        }
        
        return $avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getFacebookId(): ?string
    {
        return $this->facebookId;
    }

    public function setFacebookId(?string $facebookId): self
    {
        $this->facebookId = $facebookId;
        return $this;
    }

    public function getSsoId(): ?string
    {
        return $this->ssoId;
    }

    public function setSsoId(?string $ssoId): self
    {
        $this->ssoId = $ssoId;
        return $this;
    }

    public function getSsoProvider(): ?string
    {
        return $this->ssoProvider;
    }

    public function setSsoProvider(?string $ssoProvider): self
    {
        $this->ssoProvider = $ssoProvider;
        return $this;
    }

    public function getUserDelegate(): ?User
    {
        return $this->userDelegate;
    }

    public function setUserDelegate(?User $userDelegate): self
    {
        $this->userDelegate = $userDelegate;

        return $this;
    }

    public function getAppDelegate(): ?App
    {
        return $this->appDelegate;
    }

    public function setAppDelegate(?App $appDelegate): self
    {
        $this->appDelegate = $appDelegate;

        return $this;
    }

    public function getUnsubscribeToken(): ?string
    {
        return $this->unsubscribeToken;
    }

    public function setUnsubscribeToken(?string $unsubscribeToken): self
    {
        $this->unsubscribeToken = $unsubscribeToken;
        return $this;
    }

    public function getUnsubscribeDate(): ?\DateTimeInterface
    {
        return $this->unsubscribeDate;
    }

    public function setUnsubscribeDate(?\DateTimeInterface $unsubscribeDate): self
    {
        $this->unsubscribeDate = $unsubscribeDate;

        return $this;
    }

    public function getUnsubscribeMessage(): ?string
    {
        return $this->unsubscribeMessage;
    }

    public function setUnsubscribeMessage(?string $unsubscribeMessage): self
    {
        $this->unsubscribeMessage = $unsubscribeMessage;

        return $this;
    }

    public function getMobileRegistration(): ?int
    {
        return $this->mobileRegistration;
    }

    public function setMobileRegistration(?int $mobileRegistration): self
    {
        $this->mobileRegistration = $mobileRegistration;
        if ($this->mobileRegistration == self::MOBILE_APP_IOS || $this->mobileRegistration == self::MOBILE_APP_ANDROID) {
            $this->setMobile(true);
        }
        return $this;
    }

    public function getBackLink(): ?string
    {
        return $this->backLink;
    }

    public function setBackLink(?string $backLink): self
    {
        $this->backLink = $backLink;

        return $this;
    }

    public function isAlreadyRegistered(): ?bool
    {
        return $this->alreadyRegistered;
    }

    public function setAlreadyRegistered(?bool $alreadyRegistered): self
    {
        $this->alreadyRegistered = $alreadyRegistered;

        return $this;
    }

    public function getSolidaryUser(): ?SolidaryUser
    {
        return $this->solidaryUser;
    }

    public function setSolidaryUser(?SolidaryUser $solidaryUser): self
    {
        $this->solidaryUser = $solidaryUser;

        return $this;
    }

    public function getRefresh()
    {
        return $this->email;
    }

    public function getLastActivityDate(): ?\DateTimeInterface
    {
        return $this->lastActivityDate;
    }

    public function setLastActivityDate(?\DateTimeInterface $lastActivityDate): self
    {
        $this->lastActivityDate = $lastActivityDate;

        return $this;
    }

    public function getSolidaryStructures()
    {
        if ($this->solidaryStructures) {
            return $this->solidaryStructures;
        }
        $structures = [];
        if (!is_null($this->getOperates())) {
            foreach ($this->getOperates() as $operate) {
                $structures[] = $operate->getStructure();
            }
        }
        return $structures;
        ;
    }

    public function setSolidaryStructures(?array $solidaryStructures): self
    {
        $this->solidaryStructures = $solidaryStructures;

        return $this;
    }

    public function getAdminCommunityUser()
    {
        return $this->adminCommunityUser;
    }

    public function setAdminCommunityUser(CommunityUser $adminCommunityUser)
    {
        $this->adminCommunityUser = $adminCommunityUser;
    }

    public function getMassPerson(): ?MassPerson
    {
        return $this->massPerson;
    }

    public function setMassPerson(?MassPerson $massPerson): self
    {
        $this->massPerson = $massPerson;

        return $this;
    }

    
    public function getOperates()
    {
        return $this->operates->getValues();
    }

    public function addOperate(Operate $operate): self
    {
        if (!$this->operates->contains($operate)) {
            $this->operates[] = $operate;
            $operate->setUser($this);
        }

        return $this;
    }

    public function removeOperate(Operate $operate): self
    {
        if ($this->operates->contains($operate)) {
            $this->operates->removeElement($operate);
        }

        return $this;
    }

    public function getPaymentProfileId(): ?int
    {
        return $this->paymentProfileId;
    }

    public function setPaymentProfileId(?int $paymentProfileId): self
    {
        $this->paymentProfileId = $paymentProfileId;

        return $this;
    }

    public function getBankAccounts(): ?array
    {
        return $this->bankAccounts;
    }

    public function setBankAccounts(?array $bankAccounts): self
    {
        $this->bankAccounts = $bankAccounts;

        return $this;
    }

    public function getWallets(): ?array
    {
        return $this->wallets;
    }

    public function setWallets(?array $wallets): self
    {
        $this->wallets = $wallets;

        return $this;
    }

    public function getCarpoolExport(): ?string
    {
        return $this->carpoolExport;
    }

    public function setCarpoolExport(?string $carpoolExport): self
    {
        $this->carpoolExport = $carpoolExport;

        return $this;
    }

    public function getCanReceiveReview(): ?bool
    {
        return $this->canReceiveReview;
    }

    public function setCanReceiveReview(?bool $canReceiveReview): self
    {
        $this->canReceiveReview = $canReceiveReview;

        return $this;
    }
    
    public function isUserReviewsActive(): ?bool
    {
        return $this->userReviewsActive;
    }

    public function setUserReviewsActive(?bool $userReviewsActive): self
    {
        $this->userReviewsActive = $userReviewsActive;

        return $this;
    }

    public function isExperienced(): ?bool
    {
        return $this->experienced;
    }

    public function setExperienced(?bool $experienced): self
    {
        $this->experienced = $experienced;

        return $this;
    }

    public function getUnreadCarpoolMessageNumber(): ?int
    {
        return $this->unreadCarpoolMessageNumber;
    }

    public function setUnreadCarpoolMessageNumber(?int $unreadCarpoolMessageNumber): self
    {
        $this->unreadCarpoolMessageNumber = $unreadCarpoolMessageNumber;

        return $this;
    }


    public function getUnreadDirectMessageNumber(): ?int
    {
        return $this->unreadDirectMessageNumber;
    }

    public function setUnreadDirectMessageNumber(?int $unreadDirectMessageNumber): self
    {
        $this->unreadDirectMessageNumber = $unreadDirectMessageNumber;

        return $this;
    }

    public function getUnreadSolidaryMessageNumber(): ?int
    {
        return $this->unreadSolidaryMessageNumber;
    }

    public function setUnreadSolidaryMessageNumber(?int $unreadSolidaryMessageNumber): self
    {
        $this->unreadSolidaryMessageNumber = $unreadSolidaryMessageNumber;

        return $this;
    }

    public function getSavedCo2(): ?int
    {
        return $this->savedCo2;
    }

    public function setSavedCo2(?int $savedCo2): self
    {
        $this->savedCo2 = $savedCo2;

        return $this;
    }

    // ADMIN

    public function getImage(): ?string
    {
        if (count($this->getImages())>0 && isset($this->getImages()[0]->getVersions()['square_800'])) {
            return $this->getImages()[0]->getVersions()['square_800'];
        }
        return null;
    }

    public function getHomeAddress(): ?Address
    {
        if (is_null($this->homeAddress)) {
            foreach ($this->addresses as $address) {
                if ($address->isHome()) {
                    $this->homeAddress = $address;
                    break;
                }
            }
        }
        return $this->homeAddress;
    }

    public function setHomeAddress(?Address $homeAddress): self
    {
        $this->homeAddress = $homeAddress;

        return $this;
    }

    public function getRolesTerritory(): ?array
    {
        foreach ($this->userAuthAssignments as $userAuthAssignment) {
            if ($userAuthAssignment->getAuthItem()->getType() == AuthItem::TYPE_ROLE) {
                $this->rolesTerritory[] = [
                    'role' => $userAuthAssignment->getAuthItem()->getId(),
                    'territory' => $userAuthAssignment->getTerritory() ? $userAuthAssignment->getTerritory()->getId() : null
                ];
            }
        }
        return $this->rolesTerritory;
    }

    
    // DOCTRINE EVENTS

    /**
     * Creation date.
     *
     * @ORM\PrePersist
     */
    public function setAutoCreatedDate()
    {
        $this->setCreatedDate(new \Datetime());
    }

    /**
     * Update date.
     *
     * @ORM\PreUpdate
     */
    public function setAutoUpdatedDate()
    {
        $this->setUpdatedDate(new \Datetime());
    }
}
