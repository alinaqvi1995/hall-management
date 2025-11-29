<?php

namespace App\Services;

use App\Models\Customer;

class CustomerService
{
    public function createOrUpdate(array $data, ?int $customerId = null): Customer
    {
        if ($customerId) {
            $customer = Customer::findOrFail($customerId);
            $customer->update($data);
        } else {
            $customer = Customer::create($data);
        }

        return $customer;
    }
}
