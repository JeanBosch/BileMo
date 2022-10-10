<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ProductController extends AbstractController
{
    /**
     * @Route("/api/products", name="app_products", methods={"GET"})
     */
     
    public function getProductsList(ProductRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $products = $repository->findAll();
        $jsonProductsList = $serializer->serialize($products, 'json');
        return new JsonResponse($jsonProductsList, Response::HTTP_OK, [], true);
    }


    /**
     * @Route("/api/product/{id}", name="app_detail_product", methods={"GET"})
     */
     
    public function getDetailProduct(Product $product, SerializerInterface $serializer): JsonResponse
    {
       
        $jsonProduct = $serializer->serialize($product, 'json');
        return new JsonResponse($jsonProduct, Response::HTTP_OK, ['accept' => 'json'], true);
    }
     
}


    

