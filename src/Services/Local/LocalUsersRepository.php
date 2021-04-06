<?php

namespace Osana\Challenge\Services\Local;

use Exception;
use Osana\Challenge\Domain\Users\Company;
use Osana\Challenge\Domain\Users\Id;
use Osana\Challenge\Domain\Users\Location;
use Osana\Challenge\Domain\Users\Login;
use Osana\Challenge\Domain\Users\Name;
use Osana\Challenge\Domain\Users\Profile;
use Osana\Challenge\Domain\Users\Type;
use Osana\Challenge\Domain\Users\User;
use Osana\Challenge\Domain\Users\UsersRepository;
use Tightenco\Collect\Support\Collection;

class LocalUsersRepository implements UsersRepository
{
    public $id = null;

    public function findByLogin(Login $login, int $limit = 0): Collection
    {
        $filterCsv = array();

        foreach ($this->getUsersData() as $key => $values){

            if((stristr($values[1], $login->getValue()))){
                $this->setId($values[0]);

                foreach ($this->getProfilesData() as $k => $v){
                    if($v[0] === $this->getId()){
                        array_push($values, $v);
                    }
                }
                array_push($filterCsv, $values);
            }
        }

        foreach ($filterCsv as $key => $value){
            $users [] = new User(
                new Id($value[0]),
                new Login($value[1]),
                new Type(Type::Local()),
                new Profile(
                    new Name($value[3][0]),
                    new Company($value[3][1]),
                    new Location($value[3][2])
                )
            );
        }

        $collection = new Collection($users);

        return $collection;
    }

    public function getByLogin(Login $login, int $limit = 0): User
    {
        $filterCsv = array();
        
        foreach ($this->getUsersData() as $key => $values){

            if((($values[1] === $login->getValue()))){
                $this->setId($values[0]);
                $filterCsv = $values;

                foreach ($this->getProfilesData() as $k => $v){
                    if($v[0] === $this->getId()){
                        array_push($filterCsv, $v);
                    }
                }
            }
        }

        $users = new User(
            new Id($filterCsv[0]),
            new Login($filterCsv[1]),
            new Type(Type::Local()),
            new Profile(
                new Name($filterCsv[3][0]),
                new Company($filterCsv[3][1]),
                new Location($filterCsv[3][2])
            )
        );

        return $users;
    }

    public function add(User $user): void
    {
        $users = $this->getUsersData();
        $profiles = $this->getProfilesData();

        $this->checkExistingUser($users, $user->getLogin()->getValue());

        $newUserArray = $this->userToArray($user);

        array_push($users, $newUserArray['user']);
        array_push($profiles, $newUserArray['profile']);

        $this->writeCsv($users, 'data/users.csv');
        $this->writeCsv($profiles, 'data/profiles.csv');
    }

    public function getUsersData()
    {
        $csv = array();

        $lines = file('data/users.csv', FILE_IGNORE_NEW_LINES);

        foreach ($lines as $key => $value)
        {
            $csv[$key] = str_getcsv($value);
        }

        return $csv;
    }

    public function getProfilesData()
    {
        $csv = array();

        $lines = file('data/profiles.csv', FILE_IGNORE_NEW_LINES);

        foreach ($lines as $key => $value)
        {
            $csv[$key] = str_getcsv($value);
        }

        return $csv;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function userToArray(User $user)
    {
        return ["user" => 
            [
                $user->getId()->getValue(),
                $user->getLogin()->getValue(),
                $user->getType()->getValue(),
            ],
            "profile" =>
            [
                $user->getId()->getValue(),
                $user->getProfile()->getCompany()->getValue(),
                $user->getProfile()->getLocation()->getValue(),
                $user->getProfile()->getName()->getValue(),
            ]
        ];
    }

    public function writeCsv($users, $ruta)
    {

        if(!file_exists($ruta) ); 
            file_put_contents($ruta,'');
        $outputBuffer = fopen($ruta, 'w');

        foreach($users as $n_linea => $linea) {
            fputcsv($outputBuffer, $linea);
        }
        fclose($outputBuffer);
    }

    public function checkExistingUser($users, $login)
    {
        foreach ($users as $key => $value){
            if($value[1] == $login) {
                throw new Exception('nombre ya existe');  
            }
        }

        return true;
    }
}
