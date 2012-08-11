<?php

namespace Gitonomy\Bundle\FrontendBundle\Controller;

use Gitonomy\Bundle\CoreBundle\Entity\Email;

class EmailController extends BaseController
{
    public function activateAction($username, $hash)
    {
        $em   = $this->getDoctrine()->getEntityManager();
        $repo = $em->getRepository('GitonomyCoreBundle:Email');
        if (!$email = $repo->getEmailFromActivation($username, $hash)) {
            throw $this->createNotFoundException('There is no mail to activate with this link. Have you already activate it?');
        }

        $email->validateActivationToken($hash);
        $em->persist($email);
        $em->flush();

        return $this->render('GitonomyFrontendBundle:Email:activate.html.twig', array(
            'email' => $email
        ));
    }
}
