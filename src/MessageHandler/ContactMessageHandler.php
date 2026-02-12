<?php

namespace App\MessageHandler;

use App\Entity\Contact;
use App\Message\ContactMessage;
use App\Repository\ContactRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ContactMessageHandler
{
    public function __construct(private ContactRepository $contactRepository)
    {
    }

    public function __invoke(ContactMessage $message): void
    {
        $contact = new Contact();
        $contact->setName($message->name);
        $contact->setEmail($message->email);
        $contact->setSubject($message->subject);
        $contact->setMessage($message->message);

        $this->contactRepository->save($contact, flush: true);
    }
}
