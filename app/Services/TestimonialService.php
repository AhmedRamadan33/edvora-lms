<?php

namespace App\Services;

use App\Models\Testimonial;
use App\Repositories\TestimonialRepository;

class TestimonialService
{
    public function __construct(private TestimonialRepository $testimonials)
    {
    }

    public function paginateForAdmin(int $perPage = 20)
    {
        return $this->testimonials->paginateOrdered($perPage);
    }

    public function create(array $data, bool $isPublished = true, bool $showOnHome = true): Testimonial
    {
        $data['is_published'] = $isPublished;
        $data['show_on_home'] = $showOnHome;

        return $this->testimonials->create($data);
    }

    public function update(Testimonial $testimonial, array $data, bool $isPublished, bool $showOnHome): Testimonial
    {
        $data['is_published'] = $isPublished;
        $data['show_on_home'] = $showOnHome;

        return $this->testimonials->update($testimonial, $data);
    }

    public function delete(Testimonial $testimonial): bool
    {
        return $this->testimonials->delete($testimonial);
    }
}
