<?php

namespace App\User\Admin\Service;

use App\User\Entity\User;
use App\User\Entity\UserExport;
use App\User\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Security;

class ExportManager
{
    public const ALLOWED_FILTERS = [self::FILTER_COMMUNITY, self::FILTER_HITCHHIKING, self::FILTER_TERRITORY, self::HOME_ADDRESS_TERRITORY];
    public const SELECTED = 'selected';
    public const MAXIMUM_NUMBER_OF_ROLES = 5;
    public const FILTER_COMMUNITY = 'community';
    public const FILTER_HITCHHIKING = 'isHitchHiker';
    public const FILTER_TERRITORY = 'territory';
    public const HOME_ADDRESS_TERRITORY = 'homeAddressTerritory';

    /**
     * @var User
     */
    private $_authenticatedUser;

    /**
     * @var array
     */
    private $_filters = [];

    /**
     * @var Request
     */
    private $_request;

    /**
     * @var UserRepository
     */
    private $_userRepository;

    /**
     * @var int[]
     */
    private $_authenticatedUserRestrictionTerritories = [];

    /**
     * @var int[]
     */
    private $_territoriesFilterBases = [];

    private $_currentUser;

    /**
     * @var UserExport
     */
    private $_currentUserExport;

    /**
     * @var bool
     */
    private $_isHitchhiking;

    /**
     * @var array
     */
    private $_selected = [];

    public function __construct(
        Security $security,
        RequestStack $requestStack,
        UserRepository $userRepository
    ) {
        if (is_null($security->getUser())) {
            throw new BadRequestHttpException('There is no authenticated User');
        }

        $this->_authenticatedUser = $security->getUser();
        $this->_request = $requestStack->getCurrentRequest();
        $this->_userRepository = $userRepository;
        $this->_isHitchhiking = false;
    }

    /**
     * @return UserExport[]
     */
    public function exportExtended()
    {
        $this->_setFilters();
        $this->_setSelected();

        $this->_setAuthenticatedUserRestrictionTerritories();

        $users = $this->_userRepository->findForExport($this->_filters, $this->_authenticatedUserRestrictionTerritories, $this->_selected);

        return $this->_buildUsersToExport($users);
    }

    /**
     * @param mixed $users
     *
     * @return UserExport[]
     */
    private function _buildUsersToExport($users)
    {
        $usersToExport = [];

        foreach ($users as $user) {
            array_push(
                $usersToExport,
                UserExportMapper::fromArray($user)
            );
        }

        return $usersToExport;
    }

    private function _setAuthenticatedUserRestrictionTerritories(): self
    {
        $userTerritoryAuthAssignments = array_filter($this->_authenticatedUser->getUserAuthAssignments(), function ($assignment) {
            return 'admin_user_export_all' === $assignment->getAuthItem()->getName() && !is_null($assignment->getTerritory());
        });

        $this->_authenticatedUserRestrictionTerritories = array_map(function ($assignment) {
            return $assignment->getTerritory()->getId();
        }, $userTerritoryAuthAssignments);

        if (empty($this->_authenticatedUserRestrictionTerritories)) {
            $this->_authenticatedUserRestrictionTerritories = $this->_territoriesFilterBases;
        }

        return $this;
    }

    private function _setFilters(): self
    {
        $parameters = $this->_request->query->all();

        foreach ($parameters as $key => $parameter) {
            if (in_array($key, self::ALLOWED_FILTERS)) {
                switch ($key) {
                    case self::FILTER_HITCHHIKING:
                        $this->_isHitchhiking = true;

                        break;

                    case self::FILTER_TERRITORY:
                        $this->_territoriesFilterBases = array_map(
                            function ($id) {
                                return intval($id);
                            },
                            explode(',', $parameter)
                        );

                        break;
                }

                $this->_filters[$key] = $parameter;
            }
        }

        return $this;
    }

    private function _setSelected()
    {
        $parameters = $this->_request->query->get(self::SELECTED);
        if (!is_null($parameters) && '' !== $parameters) {
            $this->_selected = explode(',', $parameters);
        }
    }
}
