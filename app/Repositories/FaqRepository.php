<?php

namespace App\Repositories;

use App\Models\Faq;

class FaqRepository
{
    protected $model;

    public function __construct(Faq $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->orderBy('created_at', 'desc')->paginate();
    }

    public function getAllApis()
    {
        return $this->model->where('is_active', 1)->orderBy('id', 'desc')->get();
    }

    public function findById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {

        $faq = new Faq;

        foreach ($data['translations'] as $locale => $translation) {
            $faq->translateOrNew($locale)->question = $translation['question'];
            $faq->translateOrNew($locale)->answer = $translation['answer'];
        }

        $faq->save();

        return $faq;
    }

    public function update(Faq $faq, array $data)
    {

        foreach ($data['translations'] as $locale => $translation) {
            $faq->translateOrNew($locale)->question = $translation['question'];
            $faq->translateOrNew($locale)->answer = $translation['answer'];
        }

        $faq->save();

        return $faq;
    }

    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    public function toggleStatus($faq)
    {
        $faq->update(['is_active' => ! $faq->is_active]);

        return $faq;
    }

    public function find($id)
    {
        return $this->model->with('translations')->findOrFail($id);
    }
}
