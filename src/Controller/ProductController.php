<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;


class ProductController extends AbstractController
{
    /**
     * @Route("/api/products", name="app_products", methods={"GET"})
     */
     
     
    public function getProductsList(ProductRepository $repository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);
        $idCache = 'products_list_' . $page . '_' . $limit;
        $products = $cachePool->get($idCache, function (ItemInterface $item) use ($repository, $page, $limit) {
            $item->tag('productsCache');
            $item->expiresAfter(5);
            return $repository->findAllWithPagination($page, $limit);
        });

        $jsonProductsList = $serializer->serialize($products, 'json');
        return new JsonResponse($jsonProductsList, Response::HTTP_OK, [], true);
    }


    /**
     * @Route("/api/product/{id}", name="app_detail_product", methods={"GET"})
     */

     
    public function getDetailProduct(Product $product, SerializerInterface $serializer, TagAwareCacheInterface $cachePool): JsonResponse
    {
       $idCache = 'product_' . $product->getId();
         $product = $cachePool->get($idCache, function (ItemInterface $item) use ($product) {
              $item->tag('productCache');
              $item->expiresAfter(5);
              return $product;
            });
        $jsonProduct = $serializer->serialize($product, 'json');
        return new JsonResponse($jsonProduct, Response::HTTP_OK, ['accept' => 'json'], true);
    }

     /**
     * @Route("/api/product", name="app_create_product", methods={"POST"})
     * @IsGranted("ROLE_ADMIN", statusCode=403, message="Accès refusé, vous n'avez pas les droits nécessaires")
     */

    public function createProduct(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, UrlGeneratorInterface $urlGeneratorInterface ): JsonResponse
    {
       $product = $serializer->deserialize($request->getContent(), Product::class, 'json');
       $em->persist($product);
       $em->flush();

       $jsonCustomer = $serializer->serialize($product, 'json');
       $location = $urlGeneratorInterface->generate('app_detail_product', ['id' => $product->getId()]);

       return new JsonResponse($jsonCustomer, Response::HTTP_CREATED, ['Location' => $location], true);


     
}

    /**
     * @Route("/api/product/{id}", name="app_update_product", methods={"PUT"})
     * @IsGranted("ROLE_ADMIN", statusCode=403, message="Accès refusé, vous n'avez pas les droits nécessaires")
     */

    public function updateProduct(Product $product, Request $request, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $serializer->deserialize($request->getContent(), Product::class, 'json');
        $product->setModifDate(new \DateTime());
        $em->flush();

        $jsonProduct = $serializer->serialize($product, 'json');
        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/api/product/{id}", name="app_delete_product", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN", statusCode=403, message="Accès refusé, vous n'avez pas les droits nécessaires")
     */


    public function deleteProduct(Product $product, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($product);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}


    

