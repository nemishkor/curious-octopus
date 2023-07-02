<?php
declare(strict_types=1);

namespace App;

use App\Service\Encryptor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CompilerPass implements CompilerPassInterface {

    public function process(ContainerBuilder $container) {
        $vault = $container->getDefinition('secrets.vault');
        $encryptor = $container->getDefinition(Encryptor::class);
        $encryptor->setArgument(0, $vault->getArgument(0));
        $encryptor->setArgument(1, $container->getDefinition('secrets.decryption_key'));
    }

}
