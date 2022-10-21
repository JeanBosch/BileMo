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
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;




class ProductController extends AbstractController
{
    /**
     * Cette méthode permet de récupérer le catalogue des produits
     * 
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="La page que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     *
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Le nombre d'éléments que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     * 
     * @OA\Response(
     *    response=200,
     *   description="Retourne le catalogue des produits",
     * @OA\JsonContent(
     *   type="array",
     * @OA\Items(ref=@Model(type=Product::class))
     * )
     * )
     * 
     * @OA\Tag(name="Products")
     *
     * @param ProductRepository $Repository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     * 
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
     * 
     * Cette méthode permet de récupérer les informations d'un produit
     * 
     * @OA\Response(
     *    response=200,
     *   description="Retourne les informations du produit",
     * @OA\JsonContent(
     *   type="array",
     * @OA\Items(ref=@Model(type=Product::class))
     * )
     * )
     * 
     * @OA\Tag(name="Products")
     *
     * @param Product $product
     * @param SerializerInterface $serializer
     * @return JsonResponse
     * 
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
     * 
     * Cette méthode permet de créer un produit
     * 
     * @OA\RequestBody(
     *   required=true,
     * 
    
     *  @OA\JsonContent(ref=@Model(type=Product::class))
     * )
     * 
     * 
     * 
     * @OA\Response(
     *    response=201,
     *   description="Retourne le produit créé",
     * @OA\JsonContent(
     *   type="array",
     * @OA\Items(ref=@Model(type=Product::class))
     * )
     * )
     * 
     * @OA\Tag(name="Products")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     * 
     * @Route("/api/product", name="app_create_product", methods={"POST"})
     * @IsGranted("ROLE_ADMIN", statusCode=403, message="Accès refusé, vous n'avez pas les droits nécessaires")
     */

    public function createProduct(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, UrlGeneratorInterface $urlGeneratorInterface, ValidatorInterface $validatorInterface): JsonResponse
    {


        $product = $serializer->deserialize($request->getContent(), Product::class, 'json');
        $product->setCreationDate(new \DateTime());
        $errors = $validatorInterface->validate($product);
        if (count($errors) > 0) {
            return $this->json($errors, 400);
        } else {
            $em->persist($product);
            $em->flush();
        }

        $jsonCustomer = $serializer->serialize($product, 'json');
        $location = $urlGeneratorInterface->generate('app_detail_product', ['id' => $product->getId()]);

        return new JsonResponse($jsonCustomer, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    /**
     * 
     * Cette méthode permet de modifier un produit (mettre le bon id dans le formulaire)
     * 
     * @OA\RequestBody(
     *  required=true,
     * 
     * @OA\JsonContent(ref=@Model(type=Product::class))
     * )
     * 
     * 
     * @OA\Response(
     *   response=204,
     *  description="Retourne le produit modifié",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref=@Model(type=Product::class))
     * )
     * )
     * 
     * @OA\Tag(name="Products")
     * 
     * 
     * @Route("/api/product/{id}", name="app_update_product", methods={"PUT"})
     * @IsGranted("ROLE_ADMIN", statusCode=403, message="Accès refusé, vous n'avez pas les droits nécessaires")
     */

    public function updateProduct(Product $product, Request $request, EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        $serializer->deserialize($request->getContent(), Product::class, 'json');
        $product->setModifDate(new \DateTime());
        $errors = $validator->validate($product);
        if (count($errors) > 0) {
            return $this->json($errors, 400);
        } else {
            $em->persist($product);
            $em->flush();
        }

        $jsonProduct = $serializer->serialize($product, 'json');
        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }

    /**
     * 
     * Cette méthode permet de supprimer un produit
     * 
     * @OA\Response(
     *  response=204,
     * description="Retourne le produit supprimé",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref=@Model(type=Product::class))
     * )
     * )
     * 
     *  @OA\Tag(name="Products")
     * 
     * 
     * 
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
