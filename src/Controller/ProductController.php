<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/')]
final class ProductController extends AbstractController{
    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        $page = (int) $request->query->get('page', 1);
        $limit = 5;
        $orderBy = $request->query->get('sort', 'id');
        $direction = $request->query->get('direction', 'desc');
        
        $price = $request->query->get('price', '');
        $stockQuantity = $request->query->get('stockQuantity', '');
        $createdAt = $request->query->get('createdAt', '');
        
        $paginationData = $productRepository->findBySortWithPaginationAndSearch(
            $orderBy, 
            $direction, 
            $page, 
            $limit, 
            $price,
            $stockQuantity,
            $createdAt
        );

        $products = $paginationData['products'];
        $totalResults = $paginationData['totalResults'];
        $totalPages = ceil($totalResults / $limit);

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'orderBy' => $orderBy,
            'direction' => $direction,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'price' => $price,
            'stockQuantity' => $stockQuantity,
            'createdAt' => $createdAt,
        ]);
    }

    #[Route('/products/export', name: 'app_product_export')]
    public function export(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        $response = new StreamedResponse(function () use ($products) {
            $handle = fopen('php://output', 'w+');
            
            if ($handle === false) {
                throw new \Exception('Unable to open PHP output stream.');
            }

            fputcsv($handle, ['ID', 'Name', 'Description', 'Price', 'Stock Quantity', 'Created At']);

            foreach ($products as $product) {
                fputcsv($handle, [
                    $product->getId(),
                    $product->getName(),
                    $product->getDescription(),
                    $product->getPrice(),
                    $product->getStockQuantity(),
                    $product->getCreatedDate()->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="products.csv"');
        
        return $response;
    }

    #[Route('/products/import', name: 'app_product_import')]
    public function import(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $errors = [];

        if ($request->isMethod('POST') && $request->files->get('file')) {
            $file = $request->files->get('file');

            $constraints = new Assert\File([
                'maxSize' => '5M',
                'mimeTypes' => ['text/csv', 'application/vnd.ms-excel'],
                'mimeTypesMessage' => 'Please upload a valid CSV file.',
            ]);

            $violations = $validator->validate($file, $constraints);

            if (count($violations) > 0) {
                foreach ($violations as $violation) {
                    $errors[] = $violation->getMessage();
                }

                return $this->render('product/import.html.twig', [
                    'errors' => $errors,
                ]);
            }

            try {
                $spreadsheet = IOFactory::load($file->getPathname());
                $sheet = $spreadsheet->getActiveSheet();

                foreach ($sheet->getRowIterator() as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);

                    $data = [];
                    foreach ($cellIterator as $cell) {
                        $data[] = $cell->getFormattedValue();
                    }

                    if ($data[0] === 'ID') {
                        continue;
                    }

                    $product = new Product();
                    $product->setName($data[1]);
                    $product->setDescription($data[2]);
                    $product->setPrice($data[3]);
                    $product->setStockQuantity($data[4]);
                    $product->setCreatedDate(new \DateTimeImmutable($data[5]));

                    $entityManager->persist($product);
                }

                $entityManager->flush();

                $this->addFlash('success', 'Products imported successfully.');
                return $this->redirectToRoute('app_product_index');
            } catch (FileException $e) {
                $this->addFlash('error', 'An error occurred while importing the file.');
            }
        }

        return $this->render('product/import.html.twig', [
            'errors' => $errors,
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        $product->setCreatedDate(new \DateTimeIMMUTABLE('now', new \DateTimeZone('Asia/Singapore')));

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Product created successfully!');

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Product updated successfully!');

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
            $this->addFlash('success', 'Product deleted successfully!');
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
