<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Shop;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ProductController.php',
        ]);
    }

    #[Route('/api/products', name: 'products.getAll', methods: ['GET'])]
    public function getAllProducts(
        ProductRepository $repository,
        Request $request,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $idCache = 'getProduct';

        $jsonProducts = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer, $request) {
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 5);
            $limit = min($limit, 20);

            $item->tag("getProduct");
            $context = SerializationContext::create()->setGroups('getAllProducts');

            $shops = $repository->findProducts($page, $limit);
            return $serializer->serialize($shops, 'json', $context);
        });

        return new JsonResponse($jsonProducts, Response::HTTP_OK, [], true);
    }

    #[Route('/api/product/{idProduct}', name: 'products.getproduct', methods: ['GET'])]
    #[ParamConverter("product", options: ["id" => "idProduct"], class: 'App\Entity\Product')]
    public function getProduct(
        Product $product,
        Request $request,
        ProductRepository $repository,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $idCache = 'getProduct';
        $jsonProduct = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer, $request, $product) {
            $item->tag("getProduct");
            $context = SerializationContext::create()->setGroups('getProduct');

            $products = $repository->find($product);
            return $serializer->serialize($products, 'json', $context);
        });

        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    #[Route('/api/product/{idProduct}', name: 'products.deleteProduct', methods: ['DELETE'])]
    #[ParamConverter("product", options: ["id" => "idProduct"], class: 'App\Entity\Product')]
    public function deleteProduct(
        Product $product,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $cache->invalidateTags(["getProduct"]);
        $product->setStatus("0");
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_OK);
    }

    #[Route('/api/product', name: '$product.create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas admin')]
    public function createProduct(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validator,
    ) :JsonResponse
    {
        $product = $serializer->deserialize($request->getContent(), Product::class, 'json');
        $product->setStatus(true);

        //$content = $request->toArray();
        //$idCategorie = $content["idCategorie"];

        $erors = $validator->validate($product);
        if ($erors->count() >0) {
            return new JsonResponse($serializer->serialize($erors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($product);
        $entityManager->flush();

        $location = $urlGenerator->generate("products.getProduct", ['idProduct' => $product->getId(), UrlGeneratorInterface::ABSOLUTE_URL]);
        $jsonProduct = $serializer->serialize($product, 'json', ['groups' => 'getProduct']);
        return new JsonResponse($jsonProduct, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/product/{id}', name: 'products.updateProduct', methods: ['PATCH'])]
    #[ParamConverter("product", options: ["id" => "idProduct"], class: 'App\Entity\product')]
    public function updateProduct(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validator,
    ) :JsonResponse
    {
        $product = $serializer->deserialize($request->getContent(), Product::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $product]);
        $product->setStatus(true);

        $content = $request->toArray();
        //$idBoutique = $content['idBoutique'];
        //$product->addBoutiqueCategorie($categorieRepository->find($idBoutique));

        $erors = $validator->validate($product);
        if ($erors->count() >0) {
            return new JsonResponse($serializer->serialize($erors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($product);
        $entityManager->flush();

        $location = $urlGenerator->generate("products.getProduct", ['idProduct' => $product->getId(), UrlGeneratorInterface::ABSOLUTE_URL]);
        $jsonproduct = $serializer->serialize($product, "json", ['groups' => 'getProduct']);
        return new JsonResponse($jsonproduct, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/products/shop/{shop}', name: 'products.getproductbyshop', methods: ['GET'])]
    #[ParamConverter("shop", options: ["id" => "shop"], class: 'App\Entity\Product')]
    public function getProductByShop(
        Shop $shop,
        ProductRepository $repository,
        Request $request,
    ) :JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);
        $limit = $limit > 20 ? 20 : $limit;
        return $this->json($repository->findProductsByShop($page, $limit, $shop->getId()), 200, [], ['groups' => 'getAllShops']);
    }

    #[Route('/api/products/category/{category}', name: 'products.getproductbycategory', methods: ['GET'])]
    #[ParamConverter("category", options: ["id" => "category"], class: 'App\Entity\Product')]
    public function getProductByCategory(
        Category $category,
        ProductRepository $repository,
        Request $request,
    ) :JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);
        $limit = $limit > 20 ? 20 : $limit;
        return $this->json($repository->findProductsByCategory($page, $limit, $category->getId()), 200, [], ['groups' => 'getAllShops']);
    }
}
