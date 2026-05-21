export type CepAddress = {
  cep: string;
  logradouro: string;
  bairro: string;
  cidade: string;
  uf: string;
};

export type Customer = {
  id: number;
  nome: string;
  email: string;
  cep: string;
  logradouro: string;
  numero: string;
  complemento: string | null;
  bairro: string;
  cidade: string;
  uf: string;
  created_at: string;
  updated_at: string;
};

export type CustomerPayload = {
  nome: string;
  email: string;
  cep: string;
  logradouro: string;
  numero: string;
  complemento: string;
  bairro: string;
  cidade: string;
  uf: string;
};

export type CustomerFormState = CustomerPayload;

export type CustomersResponse = {
  data: Customer[];
};

export type CustomerMutationResponse = {
  message: string;
  data: Customer;
};

export type CreateCustomerResponse = CustomerMutationResponse;

export type UpdateCustomerResponse = CustomerMutationResponse;

export type DeleteCustomerResponse = {
  message: string;
};

export type ApiValidationErrors = Record<string, string[]>;

export type ApiErrorResponse = {
  message?: string;
  errors?: ApiValidationErrors;
};