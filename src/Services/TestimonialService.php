<?php

namespace App\Services;

class TestimonialService
{
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
            return null;
        }
    }
}
