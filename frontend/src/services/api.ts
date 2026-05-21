import { API_BASE_URL } from "../config/env";
import type {
  ApiErrorResponse,
  ApiValidationErrors,
  CepAddress,
  CreateCustomerResponse,
  CustomerPayload,
  CustomersResponse,
  DeleteCustomerResponse,
  UpdateCustomerResponse,
} from "../types/customer";

export class ApiError extends Error {
  status: number;
  errors?: ApiValidationErrors;

  constructor(message: string, status: number, errors?: ApiValidationErrors) {
    super(message);
    this.name = "ApiError";
    this.status = status;
    this.errors = errors;
  }
}

async function parseJsonSafely(response: Response): Promise<unknown> {
  const contentType = response.headers.get("content-type") || "";

  if (!contentType.includes("application/json")) {
    return null;
  }

  return response.json();
}

async function requestJson<T>(path: string, init?: RequestInit): Promise<T> {
  const hasBody = init?.body !== undefined;

  const response = await fetch(`${API_BASE_URL}${path}`, {
    headers: {
      Accept: "application/json",
      ...(hasBody ? { "Content-Type": "application/json" } : {}),
      ...(init?.headers || {}),
    },
    ...init,
  });

  const payload = await parseJsonSafely(response);

  if (!response.ok) {
    const errorPayload = payload as ApiErrorResponse | null;

    const validationMessage = errorPayload?.errors
      ? Object.values(errorPayload.errors).flat().join(" ")
      : undefined;

    throw new ApiError(
      validationMessage || errorPayload?.message || "Não foi possível concluir a solicitação.",
      response.status,
      errorPayload?.errors,
    );
  }

  return payload as T;
}

export async function getCepAddress(cep: string): Promise<CepAddress> {
  return requestJson<CepAddress>(`/ceps/${encodeURIComponent(cep)}`);
}

export async function getCustomers(): Promise<CustomersResponse> {
  return requestJson<CustomersResponse>("/customers");
}

export async function createCustomer(payload: CustomerPayload): Promise<CreateCustomerResponse> {
  return requestJson<CreateCustomerResponse>("/customers", {
    method: "POST",
    body: JSON.stringify(payload),
  });
}

export async function updateCustomer(
  customerId: number,
  payload: CustomerPayload,
): Promise<UpdateCustomerResponse> {
  return requestJson<UpdateCustomerResponse>(`/customers/${customerId}`, {
    method: "PUT",
    body: JSON.stringify(payload),
  });
}

export async function deleteCustomer(customerId: number): Promise<DeleteCustomerResponse> {
  return requestJson<DeleteCustomerResponse>(`/customers/${customerId}`, {
    method: "DELETE",
  });
}