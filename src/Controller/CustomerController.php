<?php

namespace App\Controller;

use App\Repository\CustomerRepository;
use App\Entity\Customer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CustomerController extends AbstractController
{
    /**
     * @Route("/api/customers", name="app_customers", methods={"GET"})
     * @IsGranted("ROLE_ADMIN", statusCode=403, message="Accès refusé, vous n'avez pas les droits nécessaires")
     */
     
    public function getCustomersList(CustomerRepository $repository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);
        $idCache = 'customers_list_' . $page . '_' . $limit;
        $customers = $cachePool->get($idCache, function (ItemInterface $item) use ($repository, $page, $limit) {
            $item->tag('customersCache');
            $item->expiresAfter(5);
            return $repository->findAllWithPagination($page, $limit);
        });
        $context = SerializationContext::create()->setGroups(['getCustomersList']);
        $jsonProductsList = $serializer->serialize($customers, 'json', $context);
        return new JsonResponse($jsonProductsList, Response::HTTP_OK, [], true);
    }


    /**
     * @Route("/api/customer/{id}", name="app_detail_customer", methods={"GET"})
     * @IsGranted("ROLE_ADMIN", statusCode=403, message="Accès refusé, vous n'avez pas les droits nécessaires")
     */
     
    public function getDetailCustomer(Customer $customer, SerializerInterface $serializer, TagAwareCacheInterface $cachePool): JsonResponse
    {
       $idCache = 'customer_' . $customer->getId();
         $customer = $cachePool->get($idCache, function (ItemInterface $item) use ($customer) {
              $item->tag('customerCache');
              $item->expiresAfter(5);
              return $customer;
            });
        $context = SerializationContext::create()->setGroups(['getCustomersList']);
        $jsonProduct = $serializer->serialize($customer, 'json', $context);
        return new JsonResponse($jsonProduct, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    
     
}