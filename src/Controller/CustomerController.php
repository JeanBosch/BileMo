<?php

namespace App\Controller;

use App\Repository\CustomerRepository;
use App\Entity\Customer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CustomerController extends AbstractController
{
    /**
     * @Route("/api/customers", name="app_customers", methods={"GET"})
     */
     
    public function getCustomersList(CustomerRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $customers = $repository->findAll();
        $jsonProductsList = $serializer->serialize($customers, 'json', ['groups' => 'getCustomersList']);
        return new JsonResponse($jsonProductsList, Response::HTTP_OK, [], true);
    }


    /**
     * @Route("/api/customer/{id}", name="app_detail_customer", methods={"GET"})
     */
     
    public function getDetailCustomer(Customer $customer, SerializerInterface $serializer): JsonResponse
    {
       
        $jsonProduct = $serializer->serialize($customer, 'json', ['groups' => 'getCustomersList']);
        return new JsonResponse($jsonProduct, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    
     
}