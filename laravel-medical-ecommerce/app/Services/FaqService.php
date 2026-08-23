<?php

namespace App\Services;

use App\Repositories\FaqRepository;

class FaqService
{
    protected FaqRepository $faqRepository;

    public function __construct(FaqRepository $faqRepository)
    {
        $this->faqRepository = $faqRepository;
    }

    public function getAllFaqs()
    {
        return $this->faqRepository->getAll();
    }

    public function getActiveFaqs()
    {
        return $this->faqRepository->getActive();
    }

    public function getFaqById($id)
    {
        return $this->faqRepository->findById($id);
    }

    public function createFaq(array $data)
    {
        return $this->faqRepository->create($data);
    }

    public function updateFaq($id, array $data)
    {
        $faq = $this->faqRepository->findById($id);

        return $this->faqRepository->update($faq, $data);
    }

    public function deleteFaq($id)
    {
        $faq = $this->faqRepository->findById($id);

        return $this->faqRepository->delete($faq);
    }
}
