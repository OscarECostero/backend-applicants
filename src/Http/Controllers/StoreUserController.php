<?php

namespace Osana\Challenge\Http\Controllers;

use Osana\Challenge\Domain\Users\Company;
use Osana\Challenge\Domain\Users\Id;
use Osana\Challenge\Domain\Users\Location;
use Osana\Challenge\Domain\Users\Login;
use Osana\Challenge\Domain\Users\Name;
use Osana\Challenge\Domain\Users\Profile;
use Osana\Challenge\Domain\Users\Type;
use Osana\Challenge\Domain\Users\User;
use Osana\Challenge\Services\Local\LocalUsersRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class StoreUserController
{
    /** @var LocalUsersRepository */
    private $localUsersRepository;

    public function __construct(LocalUsersRepository $localUsersRepository)
    {
        $this->localUsersRepository = $localUsersRepository;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $data = json_decode($request->getBody());

        $this->validateInputFields($data);

        $users = $this->getUsersData();

        $user = new User(
            new Id("CSV" . count($users)),
            new Login($data->login),
            new Type(Type::Local()),
            new Profile(
                new Name($data->profile->name),
                new Company($data->profile->company),
                new Location($data->profile->location)
            )
        );

        $save = $this->localUsersRepository->add($user);
        
        $res = [
            'id' => $user->getId()->getValue(),
            'login' => $user->getLogin()->getValue(),
            'type' => $user->getType()->getValue(),
            'profile' => [
                'name' => $user->getProfile()->getName()->getValue(),
                'company' => $user->getProfile()->getCompany()->getValue(),
                'location' => $user->getProfile()->getLocation()->getValue(),
            ]
        ];

        $res = json_encode($res);

        $response->getBody()->write($res);

        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus(200, 'OK'); 
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

    public function validateInputFields($data)
    {
        //to do
    }
}
