<?php

namespace App\Controller;

use App\Entity\Classroom;
use App\Repository\ClassroomRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ClassroomController extends AbstractController
{
    #[Route('/classrooms', name: 'app_classroom_list', methods: ['GET'])]
    public function list(ClassroomRepository $classroomRepository): JsonResponse
    {
        $classrooms = $classroomRepository->findAll();
        return $this->json($classrooms);
    }

    #[Route('/classrooms/{id}', name: 'app_classroom_retrieve', methods: ['GET'])]
    public function retrieve(Classroom $classroom): Response|JsonResponse
    {
        return $this->json($classroom);
    }

    #[Route('/classrooms', name: 'app_classroom_create', methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        ClassroomRepository $classroomRepository,
    ): JsonResponse
    {
        $classroom = $serializer->deserialize($request->getContent(), Classroom::class, 'json');
        $classroomRepository->save($classroom, true);

        return $this->json($classroom, Response::HTTP_CREATED);
    }

    #[Route('/classrooms/{id}', name: 'app_classroom_update', methods: ['PUT'])]
    public function update(
        Classroom $classroom,
        Request $request,
        SerializerInterface $serializer,
        ClassroomRepository $classroomRepository,
    ): JsonResponse
    {
        $newClassroom = $serializer->deserialize($request->getContent(), Classroom::class, 'json');

        $classroom->setName($newClassroom->getName());
        $classroom->setNumber($newClassroom->getNumber());
        $classroom->setSize($newClassroom->getSize());
        $classroomRepository->save($classroom, true);

        return $this->json($classroom, Response::HTTP_OK);
    }

    #[Route('/classrooms/{id}', name: 'app_classroom_delete', methods: ['DELETE'])]
    public function delete(Classroom $classroom, ClassroomRepository $classroomRepository,): Response|JsonResponse
    {
        $classroomRepository->remove($classroom, true);
        return new Response(null, Response::HTTP_OK);
    }
}
