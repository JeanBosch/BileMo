<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use App\Entity\Client;
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


class ClientController extends AbstractController
{
    /**
     * @Route("/api/clients", name="app_clients", methods={"GET"})
     */
     
    public function getClientsList(ClientRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $clients = $repository->findAll();
        $jsonClientsList = $serializer->serialize($clients, 'json', ['groups' => 'getClientsList']);
        return new JsonResponse($jsonClientsList, Response::HTTP_OK, [], true);
    }


    /**
     * @Route("/api/client/{id}", name="app_detail_client", methods={"GET"})
     */
     
    public function getDetailClient(Client $client, SerializerInterface $serializer): JsonResponse
    {
       
        $jsonClient = $serializer->serialize($client, 'json', ['groups' => 'getClientsList']);
        return new JsonResponse($jsonClient, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
     * @Route("/api/client/{id}/customers", name="app_customers_by_client", methods={"GET"})
     */
     
    public function getCustomersByClient(Client $id, CustomerRepository $repository, SerializerInterface $serializer ): JsonResponse
    {
        $customers = $repository->findBy(['vendor' => $id]);
        $jsonCustomersByClientList = $serializer->serialize($customers, 'json', ['groups' => 'getCustomersList']);
        return new JsonResponse($jsonCustomersByClientList, Response::HTTP_OK, [], true);  
        
    }

    /**
     * @Route("/api/client/{id}/customer/{id_customer}", name="app_detail_customer_by_client", methods={"GET"})
     */
     
    public function getDetailCustomerbyClient(Client $id, CustomerRepository $repository, SerializerInterface $serializer, Customer $id_customer ): JsonResponse
    {
        $customer = $repository->findBy(['id' => $id_customer, 'vendor' => $id]);
        $jsonCustomerByClientList = $serializer->serialize($customer, 'json', ['groups' => 'getCustomersList']);
        return new JsonResponse($jsonCustomerByClientList, Response::HTTP_OK, [], true);  
         
        
    }

    
     /**
     * @Route("/api/client/{id}/customer/{id_customer}", name="app_delete_customer", methods={"DELETE"})
     */

    public function deleteCustomerByClient(Client $id, CustomerRepository $repository, Customer $id_customer, EntityManagerInterface $em ): JsonResponse
    {
       $customer = new Customer();
       $customer = $repository->findOneBy(['id' => $id_customer, 'vendor' => $id]);
       $em->remove($customer);
       $em->flush();

       return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    
}
   

    /**
     * @Route("/api/client/{id}/customer", name="app_create_customer_by_client", methods={"POST"})
     */

     public function createCustomerByClient(Request $request, Client $id, CustomerRepository $repository, Customer $id_customer, EntityManagerInterface $em, SerializerInterface $serializer, UrlGeneratorInterface $urlGeneratorInterface ): JsonResponse
     {
        $customer = $serializer->deserialize($request->getContent(), Customer::class, 'json');
        $customer->setVendor($id);
        $em->persist($customer);
        $em->flush();

        $jsonCustomer = $serializer->serialize($customer, 'json', ['groups' => 'getCustomersList']);
        $location = $urlGeneratorInterface->generate('app_detail_customer_by_client', ['id' => $id->getId(), 'id_customer' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonCustomer, Response::HTTP_CREATED, ['Location' => $location], true);

}



}