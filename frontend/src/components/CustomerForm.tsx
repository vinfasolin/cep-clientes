import type { FormEvent } from "react";
import type { CustomerFormState } from "../types/customer";

type CustomerFormProps = {
  form: CustomerFormState;
  loadingCep: boolean;
  saving: boolean;
  onChange: (field: keyof CustomerFormState, value: string) => void;
  onSubmit: (event: FormEvent<HTMLFormElement>) => void;
  onReset: () => void;
  eyebrow?: string;
  title?: string;
  submitLabel?: string;
  resetLabel?: string;
};

export function CustomerForm({
  form,
  loadingCep,
  saving,
  onChange,
  onSubmit,
  onReset,
  eyebrow = "Cadastro",
  title = "Novo cliente",
  submitLabel = "Salvar cadastro",
  resetLabel = "Limpar",
}: CustomerFormProps) {
  return (
    <section className="card" aria-labelledby="form-title">
      <div className="section-heading">
        <div>
          <span className="eyebrow">{eyebrow}</span>
          <h2 id="form-title">{title}</h2>
        </div>

        {loadingCep && <span className="status-pill">Buscando CEP...</span>}
      </div>

      <form className="customer-form" onSubmit={onSubmit} noValidate>
        <label className="field field--span-2">
          <span>Nome</span>
          <input
            value={form.nome}
            onChange={(event) => onChange("nome", event.target.value)}
            placeholder="Ex.: Maria Oliveira"
            autoComplete="name"
            maxLength={120}
            disabled={saving}
            required
          />
        </label>

        <label className="field field--span-2">
          <span>E-mail</span>
          <input
            type="email"
            value={form.email}
            onChange={(event) => onChange("email", event.target.value)}
            placeholder="maria@email.com"
            autoComplete="email"
            maxLength={160}
            disabled={saving}
            required
          />
        </label>

        <label className="field">
          <span>CEP</span>
          <input
            value={form.cep}
            onChange={(event) => onChange("cep", event.target.value)}
            placeholder="00000-000"
            inputMode="numeric"
            autoComplete="postal-code"
            maxLength={9}
            disabled={saving}
            required
          />
        </label>

        <label className="field">
          <span>Número</span>
          <input
            value={form.numero}
            onChange={(event) => onChange("numero", event.target.value)}
            placeholder="123"
            autoComplete="address-line2"
            maxLength={20}
            disabled={saving}
            required
          />
        </label>

        <label className="field field--span-2">
          <span>Logradouro</span>
          <input
            value={form.logradouro}
            onChange={(event) => onChange("logradouro", event.target.value)}
            placeholder="Rua, avenida, praça..."
            autoComplete="address-line1"
            maxLength={180}
            disabled={saving}
            required
          />
        </label>

        <label className="field">
          <span>Bairro</span>
          <input
            value={form.bairro}
            onChange={(event) => onChange("bairro", event.target.value)}
            placeholder="Bairro"
            maxLength={120}
            disabled={saving}
            required
          />
        </label>

        <label className="field">
          <span>Cidade</span>
          <input
            value={form.cidade}
            onChange={(event) => onChange("cidade", event.target.value)}
            placeholder="Cidade"
            maxLength={120}
            disabled={saving}
            required
          />
        </label>

        <label className="field">
          <span>UF</span>
          <input
            value={form.uf}
            onChange={(event) => onChange("uf", event.target.value.toUpperCase().slice(0, 2))}
            placeholder="PR"
            maxLength={2}
            disabled={saving}
            required
          />
        </label>

        <label className="field">
          <span>Complemento</span>
          <input
            value={form.complemento}
            onChange={(event) => onChange("complemento", event.target.value)}
            placeholder="Apto, bloco, referência..."
            maxLength={120}
            disabled={saving}
          />
        </label>

        <div className="form-actions field--span-2">
          <button className="button button--ghost" type="button" onClick={onReset} disabled={saving}>
            {resetLabel}
          </button>

          <button className="button button--primary" type="submit" disabled={saving || loadingCep}>
            {saving ? "Salvando..." : submitLabel}
          </button>
        </div>
      </form>
    </section>
  );
}