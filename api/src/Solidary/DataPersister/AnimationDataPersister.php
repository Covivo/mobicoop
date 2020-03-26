<?php
namespace App\Solidary\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Action\Entity\Animation;
use App\Action\Exception\ActionException;
use App\Action\Service\AnimationManager;
use App\App\Entity\App;
use Symfony\Component\Security\Core\Security;

final class AnimationDataPersister implements ContextAwareDataPersisterInterface
{
    private $animationManager;
    private $security;
    
    public function __construct(AnimationManager $animationManager, Security $security)
    {
        $this->animationManager = $animationManager;
        $this->security = $security;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Animation;
    }

    public function persist($data, array $context = [])
    {
        if (!($data instanceof Animation)) {
            throw new ActionException(ActionException::INVALID_DATA_PROVIDED);
        }

        /**
         * @var Animation $data
         */

        // We set the correct author
        if ($this->security->getUser() instanceof App) {
            $data->setAuthor($data->getUser());
        } else {
            $data->setAuthor($this->security->getUser());
        }

        if (isset($context['collection_operation_name']) &&  $context['collection_operation_name'] == 'post') {
            $data = $this->animationManager->treatAnimation($data);
        }
        return $data;
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
