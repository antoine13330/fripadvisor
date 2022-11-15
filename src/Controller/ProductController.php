<?php

namespace App\Controller;

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
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);
        $limit = $limit > 20 ? 20 : $limit;

        $idCache = 'getAllProducts';
        $jsonProduct = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer) {
            echo "MISE EN CACHE";
            $item->tag('ProductCache');

            $product = $repository->findAll();
            return $serializer->serialize($product, 'json', ['groups' => 'getAllProducts']);

        } );
        return new JsonResponse($jsonProduct, 200, [], true);

        // return $this->json($repository->findProducts($page, $limit), 200, [], ['groups' => 'getAllProducts']);
    }

    #[Route('/api/product/{idProduct}', name: 'products.getProduct', methods: ['GET'])]
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
    #[ParamConverter("Product", options: ["id" => "idProduct"], class: 'App\Entity\Product')]
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

        $erors = $validator->validate($product);
        if ($erors->count() >0) {
            return new JsonResponse($serializer->serialize($erors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($product);
        $entityManager->flush();

        $context = SerializationContext::create()->setGroups(["getProduct"]);

        $location = $urlGenerator->generate("products.getProduct", ['idProduct' => $product->getId(), UrlGeneratorInterface::ABSOLUTE_URL]);
        $jsonProduct = $serializer->serialize($product, 'json', $context);
        return new JsonResponse($jsonProduct, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    // update route
    #[Route('/api/product/{id}', name: 'Product.update', methods: ['PUT'])]
    public function updateProduct(
        Product $Product,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ProductRepository $productRepository,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse {
        $Product = $serializer->deserialize(
            $request->getContent(),
            Product::class,
            'json',
        );
        $Product->setStatus("1");

        $content = $request->toArray();
        $id = $content['idProduct'];

        $entityManager->persist($Product);
        $entityManager->flush();

        $location = $urlGenerator->generate("products.getProduct", ['idProduct' => $Product->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $context = SerializationContext::create()->setGroups(["getAllProducts"]);

        $jsonBoutique = $serializer->serialize($Product, 'json', $context);
        return new JsonResponse($jsonBoutique, Response::HTTP_CREATED, ['$location' => ''], true);
    }
}
