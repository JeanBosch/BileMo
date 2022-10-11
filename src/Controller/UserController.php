<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Entity\User;
use App\Repository\CustomerRepository;
use App\Entity\Customer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;


class UserController extends AbstractController
{
    /**
     * @Route("/api/users", name="app_users", methods={"GET"})
     */
     
    public function getClientsList(UserRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $users = $repository->findAll();
        $jsonClientsList = $serializer->serialize($users, 'json', ['groups' => 'getUsersList']);
        return new JsonResponse($jsonClientsList, Response::HTTP_OK, [], true);
    }


    /**
     * @Route("/api/user/{id}", name="app_user_client", methods={"GET"})
     */
     
    public function getDetailClient(User $user, SerializerInterface $serializer): JsonResponse
    {
       
        $jsonClient = $serializer->serialize($user, 'json', ['groups' => 'getUsersList']);
        return new JsonResponse($jsonClient, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
     * @Route("/api/user/{id}/customers", name="app_customers_by_user", methods={"GET"})
     */
     
    public function getCustomersByClient(User $id, CustomerRepository $repository, SerializerInterface $serializer ): JsonResponse
    {
        $customers = $repository->findBy(['vendor' => $id]);
        $jsonCustomersByUserList = $serializer->serialize($customers, 'json', ['groups' => 'getCustomersList']);
        return new JsonResponse($jsonCustomersByUserList, Response::HTTP_OK, [], true);  
        
    }

    /**
     * @Route("/api/user/{id}/customer/{id_customer}", name="app_detail_customer_by_user", methods={"GET"})
     */
     
    public function getDetailCustomerbyClient(User $id, CustomerRepository $repository, SerializerInterface $serializer, Customer $id_customer ): JsonResponse
    {
        $customer = $repository->findBy(['id' => $id_customer, 'vendor' => $id]);
        $jsonCustomerByUserList = $serializer->serialize($customer, 'json', ['groups' => 'getCustomersList']);
        return new JsonResponse($jsonCustomerByUserList, Response::HTTP_OK, [], true);  
         
        
    }

    
     /**
     * @Route("/api/user/{id}/customer/{id_customer}", name="app_delete_customer", methods={"DELETE"})
     */

    public function deleteCustomerByClient(User $id, CustomerRepository $repository, Customer $id_customer, EntityManagerInterface $em ): JsonResponse
    {
       $customer = new Customer();
       $customer = $repository->findOneBy(['id' => $id_customer, 'vendor' => $id]);
       $em->remove($customer);
       $em->flush();

       return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    
}
   

    /**
     * @Route("/api/user/{id}/customer", name="app_create_customer_by_user", methods={"POST"})
     */

     public function createCustomerByUser(Request $request, User $id, EntityManagerInterface $em, SerializerInterface $serializer, UrlGeneratorInterface $urlGeneratorInterface ): JsonResponse
     {
        $customer = $serializer->deserialize($request->getContent(), Customer::class, 'json');
        $customer->setVendor($id);
        $em->persist($customer);
        $em->flush();

        $jsonCustomer = $serializer->serialize($customer, 'json', ['groups' => 'getCustomersList']);
        $location = $urlGeneratorInterface->generate('app_detail_customer_by_user', ['id' => $id->getId(), 'id_customer' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonCustomer, Response::HTTP_CREATED, ['Location' => $location], true);

}



}