<?php

namespace App\DTOs;

class CustomerDTO
{
    /**
     * @var string ID del cliente (NIT o documento de identidad)
     */
    public string $id;
    
    /**
     * @var string Nombre completo o razón social del cliente
     */
    public string $name;
    
    /**
     * @var string|null Email del cliente
     */
    public ?string $email;
    
    /**
     * @var string|null Teléfono del cliente
     */
    public ?string $phone;
    
    /**
     * @var string|null Dirección del cliente
     */
    public ?string $address;
    
    /**
     * @var string|null Ciudad del cliente
     */
    public ?string $city;
    
    /**
     * @var string|null Departamento o estado del cliente
     */
    public ?string $state;
    
    /**
     * @var string|null Código postal del cliente
     */
    public ?string $postalCode;
    
    /**
     * @var string|null País del cliente
     */
    public ?string $country;
    
    /**
     * @var string|null Tipo de documento del cliente (CC, NIT, etc.)
     */
    public ?string $documentType;
    
    /**
     * Constructor del DTO del cliente
     *
     * @param string $id
     * @param string $name
     * @param string|null $email
     * @param string|null $phone
     * @param string|null $address
     * @param string|null $city
     * @param string|null $state
     * @param string|null $postalCode
     * @param string|null $country
     * @param string|null $documentType
     */
    public function __construct(
        string $id,
        string $name,
        ?string $email = null,
        ?string $phone = null,
        ?string $address = null,
        ?string $city = null,
        ?string $state = null,
        ?string $postalCode = null,
        ?string $country = 'CO',
        ?string $documentType = 'CC'
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->address = $address;
        $this->city = $city;
        $this->state = $state;
        $this->postalCode = $postalCode;
        $this->country = $country;
        $this->documentType = $documentType;
    }
    
    /**
     * Crear una instancia de CustomerDTO desde un array
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['name'],
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['city'] ?? null,
            $data['state'] ?? null,
            $data['postal_code'] ?? null,
            $data['country'] ?? 'CO',
            $data['document_type'] ?? 'CC'
        );
    }
} 