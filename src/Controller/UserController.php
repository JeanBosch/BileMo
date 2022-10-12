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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;


class UserController extends AbstractController
{
    /**
     * @Route("/api/users", name="app_users", methods={"GET"})
     * @IsGranted("ROLE_ADMIN", statusCode=403, message="Accès refusé, vous n'avez pas les droits nécessaires")
     */
     
    public function getClientsList(UserRepository $repository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);
        $idCache = 'users_list_' . $page . '_' . $limit;
        $users = $cachePool->get($idCache, function (ItemInterface $item) use ($repository, $page, $limit) {
            $item->tag('usersCache');
            $item->expiresAfter(3600);
            return $repository->findAllWithPagination($page, $limit);
        });
    
        $jsonClientsList = $serializer->serialize($users, 'json', ['groups' => 'getUsersList']);
        return new JsonResponse($jsonClientsList, Response::HTTP_OK, [], true);
    }


    /**
     * @Route("/api/user/{id}", name="app_user_client", methods={"GET"})
     * @IsGranted("ROLE_ADMIN", statusCode=403, message="Accès refusé, vous n'avez pas les droits nécessaires")
     */
     
    public function getDetailClient(User $user, SerializerInterface $serializer, TagAwareCacheInterface $cachePool): JsonResponse
    {
       $idCache = 'user_' . $user->getId();
         $user = $cachePool->get($idCache, function (ItemInterface $item) use ($user) {
              $item->tag('userCache');
              $item->expiresAfter(3600);
              return $user;
            });
        $jsonClient = $serializer->serialize($user, 'json', ['groups' => 'getUsersList']);
        return new JsonResponse($jsonClient, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
     * @Route("/api/user/{id}/customers", name="app_customers_by_user", methods={"GET"})
     * @Security("is_granted('ROLE_USER') and user === id" , statusCode=403, message="Accès refusé, vous n'avez pas les droits nécessaires")
     */
     
     
    public function getCustomersByClient(User $id, CustomerRepository $repository, SerializerInterface $serializer , TagAwareCacheInterface $cachePool ): JsonResponse
    {
        $idCache = 'customer' . $id->getId();
        $customers = $cachePool->get($idCache, function (ItemInterface $item) use ($repository, $id) {
            $item->tag('customersbyClientCache');
            $item->expiresAfter(3600);
            return $repository->findBy(['vendor' => $id]);
        });
        $customers = $repository->findBy(['vendor' => $id]);
        $jsonCustomersByUserList = $serializer->serialize($customers, 'json', ['groups' => 'getCustomersList']);
        return new JsonResponse($jsonCustomersByUserList, Response::HTTP_OK, [], true);  
        
    }

    /**
     * @Route("/api/user/{id}/customer/{id_customer}", name="app_detail_customer_by_user", methods={"GET"})
     * @Security("is_granted('ROLE_USER') and user === id" , statusCode=403, message="Accès refusé, vous n'avez pas les droits nécessaires")
     */
     
    public function getDetailCustomerbyClient(User $id, CustomerRepository $repository, SerializerInterface $serializer, Customer $id_customer , TagAwareCacheInterface $cachePool ): JsonResponse
    {
        $idCache = 'customer' . $id_customer->getId();
        $customer = $cachePool->get($idCache, function (ItemInterface $item) use ($repository, $id_customer) {
            $item->tag('customerCache');
            $item->expiresAfter(3600);
            return $repository->find($id_customer);
        });
        $customer = $repository->findBy(['id' => $id_customer, 'vendor' => $id]);
        $jsonCustomerByUserList = $serializer->serialize($customer, 'json', ['groups' => 'getCustomersList']);
        return new JsonResponse($jsonCustomerByUserList, Response::HTTP_OK, [], true);  
         
        
    }

    
     /**
     * @Route("/api/user/{id}/customer/{id_customer}", name="app_delete_customer", methods={"DELETE"})
     * @Security("is_granted('ROLE_USER') and user === id" , statusCode=403, message="Accès refusé, vous n'avez pas les droits nécessaires")
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
     * @Security("is_granted('ROLE_USER') and user === id" , statusCode=403, message="Accès refusé, vous n'avez pas les droits nécessaires")
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


     /**
     * @Route("/api/user/{id}/customer/{id_customer}", name="app_update_customer", methods={"PUT"})
     * @Security("is_granted('ROLE_USER') and user === id" , statusCode=403, message="Accès refusé, vous n'avez pas les droits nécessaires")
     */

    public function updateCustomerByClient(User $id, CustomerRepository $repository, Customer $id_customer, EntityManagerInterface $em, SerializerInterface $serializer, Request $request ): JsonResponse
    {
       $customer = new Customer();
       $customer = $repository->findOneBy(['id' => $id_customer, 'vendor' => $id]);
       $serializer->deserialize($request->getContent(), Customer::class, 'json', ['object_to_populate' => $customer]);
       $em->flush();

       return new JsonResponse(null, Response::HTTP_NO_CONTENT);


}

/**
     * @Route("/api/user/{id}", name="app_update_client", methods={"PUT"})
     * @IsGranted("ROLE_ADMIN", statusCode=403, message="Accès refusé, vous n'avez pas les droits nécessaires")
     */

    public function updateClient(User $user, EntityManagerInterface $em, SerializerInterface $serializer, Request $request ): JsonResponse
    {
       $serializer->deserialize($request->getContent(), User::class, 'json', ['object_to_populate' => $user]);
       $em->flush();

       return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

}