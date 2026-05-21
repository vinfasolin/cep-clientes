import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import type { FormEvent } from "react";
import { CustomerForm } from "./components/CustomerForm";
import { CustomerList } from "./components/CustomerList";
import { FeedbackModal } from "./components/FeedbackModal";
import {
  ApiError,
  createCustomer,
  deleteCustomer,
  getCepAddress,
  getCustomers,
  updateCustomer,
} from "./services/api";
import type { Customer, CustomerFormState } from "./types/customer";
import "./styles.css";

const emptyForm: CustomerFormState = {
  nome: "",
  email: "",
  cep: "",
  logradouro: "",
  numero: "",
  complemento: "",
  bairro: "",
  cidade: "",
  uf: "",
};

type ModalState = {
  open: boolean;
  title: string;
  message: string;
  variant: "success" | "error" | "info";
};

const closedModal: ModalState = {
  open: false,
  title: "",
  message: "",
  variant: "info",
};

function onlyDigits(value: string): string {
  return value.replace(/\D/g, "");
}

function formatCepInput(value: string): string {
  const digits = onlyDigits(value).slice(0, 8);

  if (digits.length <= 5) {
    return digits;
  }

  return `${digits.slice(0, 5)}-${digits.slice(5)}`;
}

function normalizeText(value: string): string {
  return value.trim();
}

function getErrorMessage(error: unknown): string {
  if (error instanceof ApiError) {
    return error.message;
  }

  if (error instanceof Error) {
    return error.message;
  }

  return "Ocorreu um erro inesperado. Tente novamente.";
}

function validateForm(form: CustomerFormState): string | null {
  if (normalizeText(form.nome).length < 2) {
    return "Informe um nome com pelo menos 2 caracteres.";
  }

  if (!normalizeText(form.email).includes("@")) {
    return "Informe um e-mail válido.";
  }

  if (onlyDigits(form.cep).length !== 8) {
    return "Informe um CEP com 8 dígitos.";
  }

  if (!normalizeText(form.logradouro)) {
    return "Informe o logradouro.";
  }

  if (!normalizeText(form.numero)) {
    return "Informe o número.";
  }

  if (!normalizeText(form.bairro)) {
    return "Informe o bairro.";
  }

  if (!normalizeText(form.cidade)) {
    return "Informe a cidade.";
  }

  if (!/^[A-Z]{2}$/.test(normalizeText(form.uf).toUpperCase())) {
    return "Informe a UF com 2 letras. Exemplo: PR.";
  }

  return null;
}

function customerToForm(customer: Customer): CustomerFormState {
  return {
    nome: customer.nome,
    email: customer.email,
    cep: customer.cep,
    logradouro: customer.logradouro,
    numero: customer.numero,
    complemento: customer.complemento ?? "",
    bairro: customer.bairro,
    cidade: customer.cidade,
    uf: customer.uf,
  };
}

function buildPayload(form: CustomerFormState): CustomerFormState {
  return {
    nome: normalizeText(form.nome),
    email: normalizeText(form.email).toLowerCase(),
    cep: formatCepInput(form.cep),
    logradouro: normalizeText(form.logradouro),
    numero: normalizeText(form.numero),
    complemento: normalizeText(form.complemento),
    bairro: normalizeText(form.bairro),
    cidade: normalizeText(form.cidade),
    uf: normalizeText(form.uf).toUpperCase(),
  };
}

function App() {
  const [form, setForm] = useState<CustomerFormState>(emptyForm);
  const [editForm, setEditForm] = useState<CustomerFormState>(emptyForm);
  const [customers, setCustomers] = useState<Customer[]>([]);

  const [loadingCustomers, setLoadingCustomers] = useState(true);
  const [loadingCep, setLoadingCep] = useState(false);
  const [loadingEditCep, setLoadingEditCep] = useState(false);
  const [saving, setSaving] = useState(false);
  const [savingEdit, setSavingEdit] = useState(false);
  const [deleting, setDeleting] = useState(false);

  const [editingCustomer, setEditingCustomer] = useState<Customer | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<Customer | null>(null);
  const [modal, setModal] = useState<ModalState>(closedModal);

  const lastCepLookupRef = useRef("");
  const lastEditCepLookupRef = useRef("");

  const cepDigits = useMemo(() => onlyDigits(form.cep), [form.cep]);
  const editCepDigits = useMemo(() => onlyDigits(editForm.cep), [editForm.cep]);

  const openModal = useCallback((nextModal: Omit<ModalState, "open">) => {
    setModal({ ...nextModal, open: true });
  }, []);

  const loadCustomers = useCallback(async () => {
    setLoadingCustomers(true);

    try {
      const response = await getCustomers();
      setCustomers(response.data);
    } catch (error) {
      openModal({
        title: "Não foi possível carregar a lista",
        message: getErrorMessage(error),
        variant: "error",
      });
    } finally {
      setLoadingCustomers(false);
    }
  }, [openModal]);

  useEffect(() => {
    void loadCustomers();
  }, [loadCustomers]);

  useEffect(() => {
    if (cepDigits.length !== 8) {
      return;
    }

    if (lastCepLookupRef.current === cepDigits) {
      return;
    }

    const timeoutId = window.setTimeout(async () => {
      lastCepLookupRef.current = cepDigits;
      setLoadingCep(true);

      try {
        const address = await getCepAddress(cepDigits);

        setForm((currentForm) => {
          if (onlyDigits(currentForm.cep) !== cepDigits) {
            return currentForm;
          }

          return {
            ...currentForm,
            cep: address.cep,
            logradouro: address.logradouro,
            bairro: address.bairro,
            cidade: address.cidade,
            uf: address.uf,
          };
        });
      } catch (error) {
        lastCepLookupRef.current = "";
        openModal({
          title: "CEP não encontrado",
          message: getErrorMessage(error),
          variant: "error",
        });
      } finally {
        setLoadingCep(false);
      }
    }, 400);

    return () => window.clearTimeout(timeoutId);
  }, [cepDigits, openModal]);

  useEffect(() => {
    if (!editingCustomer) {
      return;
    }

    if (editCepDigits.length !== 8) {
      return;
    }

    if (lastEditCepLookupRef.current === editCepDigits) {
      return;
    }

    const timeoutId = window.setTimeout(async () => {
      lastEditCepLookupRef.current = editCepDigits;
      setLoadingEditCep(true);

      try {
        const address = await getCepAddress(editCepDigits);

        setEditForm((currentForm) => {
          if (onlyDigits(currentForm.cep) !== editCepDigits) {
            return currentForm;
          }

          return {
            ...currentForm,
            cep: address.cep,
            logradouro: address.logradouro,
            bairro: address.bairro,
            cidade: address.cidade,
            uf: address.uf,
          };
        });
      } catch (error) {
        lastEditCepLookupRef.current = "";
        openModal({
          title: "CEP não encontrado",
          message: getErrorMessage(error),
          variant: "error",
        });
      } finally {
        setLoadingEditCep(false);
      }
    }, 400);

    return () => window.clearTimeout(timeoutId);
  }, [editCepDigits, editingCustomer, openModal]);

  function handleChange(field: keyof CustomerFormState, value: string): void {
    setForm((currentForm) => ({
      ...currentForm,
      [field]: field === "cep" ? formatCepInput(value) : value,
    }));

    if (field === "cep" && onlyDigits(value).length < 8) {
      lastCepLookupRef.current = "";
    }
  }

  function handleEditChange(field: keyof CustomerFormState, value: string): void {
    setEditForm((currentForm) => ({
      ...currentForm,
      [field]: field === "cep" ? formatCepInput(value) : value,
    }));

    if (field === "cep" && onlyDigits(value).length < 8) {
      lastEditCepLookupRef.current = "";
    }
  }

  function handleReset(): void {
    lastCepLookupRef.current = "";
    setForm(emptyForm);
  }

  function handleCloseEdit(): void {
    lastEditCepLookupRef.current = "";
    setEditingCustomer(null);
    setEditForm(emptyForm);
  }

  function handleEdit(customer: Customer): void {
    const nextForm = customerToForm(customer);

    lastEditCepLookupRef.current = onlyDigits(nextForm.cep);
    setEditForm(nextForm);
    setEditingCustomer(customer);
  }

  function handleDelete(customer: Customer): void {
    setDeleteTarget(customer);
  }

  async function handleSubmit(event: FormEvent<HTMLFormElement>): Promise<void> {
    event.preventDefault();

    const validationError = validateForm(form);

    if (validationError) {
      openModal({
        title: "Revise o cadastro",
        message: validationError,
        variant: "error",
      });
      return;
    }

    setSaving(true);

    try {
      const response = await createCustomer(buildPayload(form));

      setCustomers((currentCustomers) => [
        response.data,
        ...currentCustomers.filter((customer) => customer.id !== response.data.id),
      ]);

      handleReset();

      openModal({
        title: "Cadastro salvo",
        message: "Cliente cadastrado com sucesso.",
        variant: "success",
      });
    } catch (error) {
      openModal({
        title: "Não foi possível salvar",
        message: getErrorMessage(error),
        variant: "error",
      });
    } finally {
      setSaving(false);
    }
  }

  async function handleEditSubmit(event: FormEvent<HTMLFormElement>): Promise<void> {
    event.preventDefault();

    if (!editingCustomer) {
      return;
    }

    const validationError = validateForm(editForm);

    if (validationError) {
      openModal({
        title: "Revise o cadastro",
        message: validationError,
        variant: "error",
      });
      return;
    }

    setSavingEdit(true);

    try {
      const response = await updateCustomer(editingCustomer.id, buildPayload(editForm));

      setCustomers((currentCustomers) =>
        currentCustomers.map((customer) =>
          customer.id === response.data.id ? response.data : customer,
        ),
      );

      handleCloseEdit();

      openModal({
        title: "Cadastro atualizado",
        message: "As alterações foram salvas com sucesso.",
        variant: "success",
      });
    } catch (error) {
      openModal({
        title: "Não foi possível atualizar",
        message: getErrorMessage(error),
        variant: "error",
      });
    } finally {
      setSavingEdit(false);
    }
  }

  async function handleConfirmDelete(): Promise<void> {
    if (!deleteTarget) {
      return;
    }

    setDeleting(true);

    try {
      await deleteCustomer(deleteTarget.id);

      setCustomers((currentCustomers) =>
        currentCustomers.filter((customer) => customer.id !== deleteTarget.id),
      );

      setDeleteTarget(null);

      openModal({
        title: "Cadastro excluído",
        message: "O cliente foi removido com sucesso.",
        variant: "success",
      });
    } catch (error) {
      openModal({
        title: "Não foi possível excluir",
        message: getErrorMessage(error),
        variant: "error",
      });
    } finally {
      setDeleting(false);
    }
  }

  return (
    <main className="app-shell">
      <header className="app-header">
        <div>
          <span className="eyebrow">CEP Clientes</span>
          <h1>Cadastro rápido com busca automática de endereço</h1>
          <p>
            Digite o CEP para preencher o endereço, complete os dados do cliente e acompanhe a lista
            de cadastros.
          </p>
        </div>
      </header>

      <div className="layout">
        <CustomerForm
          form={form}
          loadingCep={loadingCep}
          saving={saving}
          onChange={handleChange}
          onSubmit={(event) => void handleSubmit(event)}
          onReset={handleReset}
        />

        <CustomerList
          customers={customers}
          loading={loadingCustomers}
          onRefresh={() => void loadCustomers()}
          onEdit={handleEdit}
          onDelete={handleDelete}
        />
      </div>

      {editingCustomer && (
        <div className="modal-backdrop" role="presentation" onMouseDown={handleCloseEdit}>
          <section
            className="edit-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="edit-customer-title"
            onMouseDown={(event) => event.stopPropagation()}
          >
            <CustomerForm
              form={editForm}
              loadingCep={loadingEditCep}
              saving={savingEdit}
              onChange={handleEditChange}
              onSubmit={(event) => void handleEditSubmit(event)}
              onReset={handleCloseEdit}
              eyebrow="Edição"
              title="Editar cliente"
              submitLabel="Salvar alterações"
              resetLabel="Cancelar"
            />
          </section>
        </div>
      )}

      <FeedbackModal
        open={Boolean(deleteTarget)}
        title="Excluir cadastro?"
        message={
          deleteTarget
            ? `Tem certeza que deseja excluir o cadastro de ${deleteTarget.nome}? Essa ação não pode ser desfeita.`
            : ""
        }
        variant="error"
        onClose={() => setDeleteTarget(null)}
        onConfirm={() => void handleConfirmDelete()}
        closeLabel="Cancelar"
        confirmLabel="Excluir"
        confirming={deleting}
      />

      <FeedbackModal
        open={modal.open}
        title={modal.title}
        message={modal.message}
        variant={modal.variant}
        onClose={() => setModal(closedModal)}
      />
    </main>
  );
}

export default App;