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
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;


class UserController extends AbstractController
{
    /**
     * 
     *  * Cette méthode permet de récupérer l'ensemble des utilisateurs
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
     *   description="Retourne la liste des clients",
     * @OA\JsonContent(
     *   type="array",
     * @OA\Items(ref=@Model(type=Product::class))
     * )
     * )
     * 
     * @OA\Tag(name="Users")
     *
     * @param UserRepository $Repository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     * 
     * 
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
            $item->expiresAfter(5);
            return $repository->findAllWithPagination($page, $limit);
        });

        $context = SerializationContext::create()->setGroups(['getUsersList']);
        $jsonClientsList = $serializer->serialize($users, 'json', $context);
        return new JsonResponse($jsonClientsList, Response::HTTP_OK, [], true);
    }


    /**
     * 
     * * Cette méthode permet de récupérer un utilisateur
     * 
     * @OA\Response(
     *   response=200,
     *  description="Retourne un utilisateur",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref=@Model(type=User::class))
     * )
     * )
     * 
     * @OA\Tag(name="Users")
     * 
     * @param User $user
     * @param SerializerInterface $serializer
     * @return JsonResponse
     * 
     * @Route("/api/user/{id}", name="app_user_client", methods={"GET"})
     * @Security("is_granted('ROLE_USER') and user === id or is_granted('ROLE_ADMIN')" , statusCode=403, message="Accès refusé, vous n'avez pas les droits nécessaires")
     */

    public function getDetailClient(User $user, SerializerInterface $serializer, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $idCache = 'user_' . $user->getId();
        $user = $cachePool->get($idCache, function (ItemInterface $item) use ($user) {
            $item->tag('userCache');
            $item->expiresAfter(5);
            return $user;
        });

        $context = SerializationContext::create()->setGroups(['getUsersList']);
        $jsonClient = $serializer->serialize($user, 'json', $context);
        return new JsonResponse($jsonClient, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
     * 
     * * Cette méthode permet de créer un utilisateur
     * 
     * @OA\Response(
     *   response=201,
     *  description="Retourne un utilisateur",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref=@Model(type=User::class))
     * )
     * )
     * 
     * @OA\RequestBody(
     *  required=true,
     * 
     * @OA\JsonContent(ref=@Model(type=User::class))
     * )
     * 
     * @OA\Tag(name="Users")
     * 
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @return JsonResponse
     * 
     * @Route("/api/user", name="app_user_create", methods={"POST"})
     * @IsGranted("ROLE_ADMIN", statusCode=403, message="Accès refusé, vous n'avez pas les droits nécessaires")
     */

     public function createUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $jsonUser = $request->getContent();
        $user = $serializer->deserialize($jsonUser, User::class, 'json');
        $user->setCreationDate(new \DateTime());
        $em->persist($user);
        $em->flush();
        $url = $urlGenerator->generate('app_user_client', ['id' => $user->getId()]);
        return new JsonResponse($serializer->serialize($user, 'json'), Response::HTTP_CREATED, ['Location' => $url], true);
    }



    /**
     * 
     *  Cette méthode permet de mettre à jour un utilisateur (mettre le bon id dans le formulaire)
     * 
     * @OA\Response(
     *  response=204,
     * description="Retourne un utilisateur mis à jour",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref=@Model(type=User::class))
     * )
     * )
     * 
     * @OA\RequestBody(
     *  required=true,
     * 
     * @OA\JsonContent(ref=@Model(type=User::class))
     * )
     * 
     * @OA\Tag(name="Users")
     * 
     * 
     * @Route("/api/user/{id}", name="app_update_client", methods={"PUT"})
     * @Security("is_granted('ROLE_USER') and user === id or is_granted('ROLE_ADMIN')" , statusCode=403, message="Accès refusé, vous n'avez pas les droits nécessaires")
     */

    public function updateClient(User $user, EntityManagerInterface $em, SerializerInterface $serializer, Request $request): JsonResponse
    {
        $serializer->deserialize($request->getContent(), User::class, 'json');
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * 
     * Cette méthode permet de supprimer un utilisateur ainsi que les customers associés 
     * 
     * @OA\Response(
     * response=204,
     * description="Retourne un utilisateur supprimé",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref=@Model(type=User::class))
     * )
     * )
     * 
     * 
     * @OA\Tag(name="Users")
     * 
     * @Route("/api/user/{id}", name="app_delete_client", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN", statusCode=403, message="Accès refusé, vous n'avez pas les droits nécessaires")
     */

    public function deleteClient(User $user, EntityManagerInterface $em): JsonResponse
    {
        $customers = $user->getCustomers();
        foreach ($customers as $customer) {
            $em->remove($customer);
        }
        $em->remove($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * 
     * Cette méthode permet de récupérer les customers d'un user
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
     * 
     * @OA\Response(
     *  response=201,
     * description="Retourne les customers d'un user",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref=@Model(type=User::class))
     * )
     * )
     * 
     * @OA\Tag(name="Users")
     * 
     * @Route("/api/user/{id}/customers", name="app_customers_by_user", methods={"GET"})
     * @Security("is_granted('ROLE_USER') and user === id" , statusCode=403, message="Accès refusé, vous n'avez pas les droits nécessaires")
     */


    public function getCustomersByClient(Request $request, User $id, CustomerRepository $repository, SerializerInterface $serializer, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 5);
        $idCache = 'customers_' . $id->getId();
       $customers= $repository->findBy(['vendor' => $id], null, $limit, ($page - 1) * $limit);
        $customers = $cachePool->get($idCache, function (ItemInterface $item) use ($customers, $page, $limit) {
            $item->tag('customersByUserCache');
            $item->expiresAfter(5);
            return $customers;
        });

        $context = SerializationContext::create()->setGroups(['getUsersList']);
        $jsonCustomersByUserList = $serializer->serialize($customers, 'json', $context);
        return new JsonResponse($jsonCustomersByUserList, Response::HTTP_OK, [], true);
    }

    /**
     * 
     * Cette méthode permet de récupérer les détails d'un customer appartenant à user défini
     * 
     * @OA\Response(
     * response=200,
     * description="Retourne les détails d'un customer appartenant à user défini",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref=@Model(type=User::class))
     * )
     * )
     * 
     * @OA\Tag(name="Users")
     * 
     * 
     * @Route("/api/user/{id}/customer/{id_customer}", name="app_detail_customer_by_user", methods={"GET"})
     * @Security("is_granted('ROLE_USER') and user === id" , statusCode=403, message="Accès refusé, vous n'avez pas les droits nécessaires")
     */

    public function getDetailCustomerbyClient(User $id, CustomerRepository $repository, SerializerInterface $serializer, Customer $id_customer, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $idCache = 'customer' . $id_customer->getId();
        $customer = $cachePool->get($idCache, function (ItemInterface $item) use ($repository, $id_customer) {
            $item->tag('customerCache');
            $item->expiresAfter(5);
            return $repository->find($id_customer);
        });
        $customer = $repository->findBy(['id' => $id_customer, 'vendor' => $id]);
        $context = SerializationContext::create()->setGroups(['getUsersList']);
        $jsonCustomerByUserList = $serializer->serialize($customer, 'json', $context);
        return new JsonResponse($jsonCustomerByUserList, Response::HTTP_OK, [], true);
    }


    /**
     * 
     * Cette méthode permet de supprimer un customer appartenant à user défini
     * 
     * @OA\Response(
     * response=204,
     * description="Supprime un customer appartenant à user défini",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref=@Model(type=User::class))
     *  )
     * )
     * 
     * @OA\Tag(name="Users")
     * 
     * @Route("/api/user/{id}/customer/{id_customer}", name="app_delete_customer", methods={"DELETE"})
     * @Security("is_granted('ROLE_USER') and user === id or is_granted('ROLE_ADMIN')" , statusCode=403, message="Accès refusé, vous n'avez pas les droits nécessaires")
     */

    public function deleteCustomerByClient(User $id, CustomerRepository $repository, Customer $id_customer, EntityManagerInterface $em): JsonResponse
    {
        $customer = new Customer();
        $customer = $repository->findOneBy(['id' => $id_customer, 'vendor' => $id]);
        $em->remove($customer);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }


    /**
     * 
     * Cette méthode permet de créer un customer pour un user défini
     * 
     * @OA\Response(
     * response=201,
     * description="Crée un customer pour à user défini",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref=@Model(type=User::class))
     * )
     * )
     * 
     * @OA\RequestBody(
     *  required=true,
     * 
     * @OA\JsonContent(ref=@Model(type=Customer::class))
     * )
     * 
     * @OA\Tag(name="Users")
     * 
     * @Route("/api/user/{id}/customer", name="app_create_customer_by_user", methods={"POST"})
     * @Security("is_granted('ROLE_USER') and user === id or is_granted('ROLE_ADMIN')" , statusCode=403, message="Accès refusé, vous n'avez pas les droits nécessaires")
     */



    public function createCustomerByUser(Request $request, User $id, EntityManagerInterface $em, SerializerInterface $serializer, UrlGeneratorInterface $urlGeneratorInterface): JsonResponse
    {
        $customer = $serializer->deserialize($request->getContent(), Customer::class, 'json');
        $customer->setCreationDate(new \DateTime());
        $customer->setVendor($id);
        $em->persist($customer);
        $em->flush();

        $context = SerializationContext::create()->setGroups(['getUsersList']);
        $jsonCustomer = $serializer->serialize($customer, 'json', $context);
        $location = $urlGeneratorInterface->generate('app_detail_customer_by_user', ['id' => $id->getId(), 'id_customer' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonCustomer, Response::HTTP_CREATED, ['Location' => $location], true);
    }


    /**
     * 
     * Cette méthode permet de modifier un customer appartenant à user défini (mettre le bon id du customer dans le formulaire)
     *
     * @OA\Response(
     * response=204,
     * description="Modifie un customer appartenant à user défini",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref=@Model(type=User::class))
     * )
     * )
     *
     * @OA\RequestBody(
     *  required=true,
     *
     * @OA\JsonContent(ref=@Model(type=Customer::class))
     * )
     *
     * @OA\Tag(name="Users")
     *   
     * @Route("/api/user/{id}/customer/{id_customer}", name="app_update_customer", methods={"PUT"})
     * @Security("is_granted('ROLE_USER') and user === id or is_granted('ROLE_ADMIN')" , statusCode=403, message="Accès refusé, vous n'avez pas les droits nécessaires")
     */

    public function updateCustomerByClient(User $id, CustomerRepository $repository, Customer $id_customer, EntityManagerInterface $em, SerializerInterface $serializer, Request $request): JsonResponse
    {

        $serializer->deserialize($request->getContent(), Customer::class, 'json');
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
