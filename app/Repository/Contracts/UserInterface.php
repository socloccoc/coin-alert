<?php

namespace App\Repository\Contracts;

use App\Repository;

interface UserInterface extends RepositoryInterface
{
    public function createNewUser($userName, $displayName);
    public function resetPassword($id, $newPassword);
    public function changePassword($username, $newpassword);
    public function findUsers($searchWord, $start,$length,$order,$orderby);
    public function updateConfirmCode($id, $confirmCode);
    public function updateDeviceIdentifier($id, $device_identifier);
    public function getDeviceIdentifierById($id);
    public function getListDeviceIdentifierByListId($listId);
    public function createNewIosUser($userName, $email);
    public function findUsersIos($searchWord, $start, $length, $order, $orderby);
    public function createUser($dataUser);
    public function informationUserConnectLineBot($clause);
    public function findWhereUsersWeb($request);
}