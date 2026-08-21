<?php

namespace App\Services;

use App\Models\ActivityLog;
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

        $testimonial = $this->testimonials->create($data);

        ActivityLog::record('testimonial.created', $testimonial, ['name' => $testimonial->name]);

        return $testimonial;
    }

    public function update(Testimonial $testimonial, array $data, bool $isPublished, bool $showOnHome): Testimonial
    {
        $data['is_published'] = $isPublished;
        $data['show_on_home'] = $showOnHome;

        $testimonial = $this->testimonials->update($testimonial, $data);

        ActivityLog::record('testimonial.updated', $testimonial, ['name' => $testimonial->name]);

        return $testimonial;
    }

    public function delete(Testimonial $testimonial): bool
    {
        $name = $testimonial->name;

        $result = $this->testimonials->delete($testimonial);

        ActivityLog::record('testimonial.deleted', $testimonial, ['name' => $name]);

        return $result;
    }
}
