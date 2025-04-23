<?php

namespace App\Service;

class CommentValidator
{
    private array $motsInterdits = [
        'insulte',
        'motgrossier',
        'spam',
        'connard',
        'idiot',
        'imbecile',
        'merde',
        'putain',
        'salope',
        'pute',
        // Ajoutez d'autres mots interdits selon vos besoins
    ];

    public function contientMotInterdit(string $contenu): ?string
    {
        $contenuMinuscule = mb_strtolower($contenu, 'UTF-8');
        
        foreach ($this->motsInterdits as $mot) {
            if (str_contains($contenuMinuscule, mb_strtolower($mot, 'UTF-8'))) {
                return $mot;
            }
        }
        
        return null;
    }
} 