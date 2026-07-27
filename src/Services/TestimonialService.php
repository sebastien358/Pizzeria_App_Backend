<?php

namespace App\Services;

use App\Entity\Picture;
use Doctrine\ORM\EntityManagerInterface;

class TestimonialService
{
    private EntityManagerInterface $entityManager;
    private FileUploader $fileUploader;

    public function __construct(EntityManagerInterface $entityManager, FileUploader $fileUploader)
    {
        $this->entityManager = $entityManager;
        $this->fileUploader = $fileUploader;
    }

    public function getTestimonialData($request, $testimonials, $serializer)
    {
        if (is_array($testimonials)) {
            $dataTestimonials = $serializer->normalize($testimonials, 'json', ['groups' => ['testimonials', 'pictures'],
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);

            $urlImage = $request->getSchemeAndHttpHost() . '/images/';

            foreach ($dataTestimonials as &$testimonial) {
                if (isset($testimonial['pictures'])) {
                    foreach ($testimonial['pictures'] as &$picture) {
                        $picture['filename'] = $urlImage . $picture['filename'];
                    }
                }
            }

            return $dataTestimonials;
        } else {
            $dataTestimonial = $serializer->normalize($testimonials, 'json', ['groups' => ['testimonials', 'pictures'],
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);

            return $dataTestimonial;
        }
    }

    public function handleTestimonialImage($image, $testimonial) {

        if (!$image) return;

        if ($image->getSize() > 5 * 1024 * 1024) {
            throw new \Exception('La taille de l\'image est trop grande'. $image->getClientOriginalName());
        }

        $picture = new Picture();

        $filename = $this->fileUploader->upload($image);

        $picture->setFilename($filename);
        $picture->setTestimonial($testimonial);

        $this->entityManager->persist($picture);
    }
}
