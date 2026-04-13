<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class RolesTransformer implements DataTransformerInterface
{
    public function transform($roles): ?string
    {
        if (empty($roles) || !is_array($roles)) {
            return null;
        }

        return $roles[0] ?? null;
    }

    public function reverseTransform($role): array
    {
        if (null === $role || '' === $role) {
            return [];
        }

        return [$role];
    }
}
