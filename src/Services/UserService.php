<?php

namespace App\Services;

class UserService
{
    public function getDataService($clients, $serializer)
    {
        if ($clients) {
            $dataClients = $serializer->normalize($clients, 'json', ['groups' => ['users'],
                'circular_reference_handlers' => function ($object) {
                    return $object->getId();
                }
            ]);

            return $dataClients;
        } else {
            $dataClient = $serializer->normalize($clients, 'json', ['groups' => ['users'],
                'circular_reference_handlers' => function ($object) {
                    return $object->getId();
                }
            ]);

            return $dataClient;
        }
    }
}
