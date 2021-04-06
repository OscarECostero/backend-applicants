<?php

namespace Osana\Challenge\Services\GitHub;

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

class GitHubUsersRepository implements UsersRepository
{
    public function findByLogin(Login $name, int $limit = 20): Collection
    {
        $opts = [
            'http' => [
                    'method' => 'GET',
                    'header' => [
                            'User-Agent: PHP',
                    ]
            ]
    ];

        $contexto = stream_context_create($opts);

        $resultado = json_decode(file_get_contents('https://api.github.com/users', false, $contexto));

        $users = array();

        foreach($resultado as $key => $value){
            array_push($users, [$value->id, $value->login, 'github', []]);

        }

        foreach ($users as $key => $value){
            $data[] = new User(
                new Id($value[0]),
                new Login($value[1]),
                new Type(Type::GitHub()),
                new Profile(
                    new Name("null"),
                    new Company("null"),
                    new Location("null")
                )
            );
        }


        $collection = new Collection($data);

        return $collection;
    }

    public function getByLogin(Login $name, int $limit = 0): User
    {
        $opts = [
            'http' => [
                    'method' => 'GET',
                    'header' => [
                            'User-Agent: PHP',
                    ]
            ]
    ];

        $contexto = stream_context_create($opts);

        $resultado = json_decode(file_get_contents('https://api.github.com/users/'.$name->getValue(), false, $contexto));

        $data = new User(
            new Id($resultado->id),
            new Login($resultado->login),
            new Type(Type::GitHub()),
            new Profile(
                new Name("null"),
                new Company("null"),
                new Location("null")
            )
        );

        return $data;    
    }

    public function add(User $user): void
    {
        throw new OperationNotAllowedException();
    }
}
