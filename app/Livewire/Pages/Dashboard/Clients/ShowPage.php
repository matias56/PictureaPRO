<?php

namespace App\Livewire\Pages\Dashboard\Clients;

use App\Models\Client;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Mary\Traits\Toast;
use Livewire\Component;

class ShowPage extends Component
{
    use Toast;

    public int $id;
    public Client $client;

    public function mount()
    {
        $client = Client::query()
            ->with(['province', 'country'])
            ->find($this->id);

        if (is_null($client)) {
            return redirect()->route('dashboard.clients.index');
        }

        $this->client = $client;
    }

    public function getItems(): array
    {
        return [
            ['label' => 'Nombre completo', 'value' => $this->client->fullname],
            ['label' => 'Correo', 'value' => $this->client->email],
            ['label' => 'NIF / DNI', 'value' => $this->client->nif_document ?? 'Sin definir'],
            ['label' => 'Teléfono', 'value' => $this->client->phone_number ?? 'Sin definir'],
            ['label' => 'Creado el', 'value' => $this->client->created_at->format('d/m/Y')],
            [],
            ['label' => 'Dirección', 'value' => $this->client->address ?? 'Sin definir'],
            ['label' => 'Ciudad', 'value' => $this->client->city_name ?? 'Sin definir'],
            ['label' => 'Provincia', 'value' => $this->client->province_name ?? 'Sin definir'],
            ['label' => 'País', 'value' => $this->client->country_name ?? 'Sin definir'],
            ['label' => 'Código Postal', 'value' => $this->client->postal_code ?? 'Sin definir'],
        ];
    }

    public function openBooking(int $calendarId, int $bookingId)
    {
        $this->dispatch(
            'bookings:open-drawer',
            calendar: $calendarId,
            action: 'edit',
            id: $bookingId
        );
    }

    #[Computed, On('bookings:updated')]
    public function bookings()
    {
        $bookings = $this->client->bookings()
            ->with(['availability', 'client', 'pack', 'pack.service', 'payment'])
            ->latest('created_at')
            ->get();

        return $bookings;
    }

    #[On('clients:deleted')]
    public function onDelete()
    {
        return redirect()->route('dashboard.clients.index');
    }

    #[On('clients:updated')]
    public function render()
    {
        $items = $this->getItems();

        return view('livewire.pages.dashboard.clients.show-page', compact('items'));
    }
}
