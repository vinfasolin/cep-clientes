import type { Customer } from "../types/customer";

type CustomerListProps = {
  customers: Customer[];
  loading: boolean;
  onRefresh: () => void;
  onEdit: (customer: Customer) => void;
  onDelete: (customer: Customer) => void;
};

function formatDate(value: string): string {
  try {
    return new Intl.DateTimeFormat("pt-BR", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    }).format(new Date(value));
  } catch {
    return value;
  }
}

export function CustomerList({
  customers,
  loading,
  onRefresh,
  onEdit,
  onDelete,
}: CustomerListProps) {
  return (
    <section className="card" aria-labelledby="list-title">
      <div className="section-heading">
        <div>
          <span className="eyebrow">Registros</span>
          <h2 id="list-title">Clientes cadastrados</h2>
        </div>

        <button
          className="button button--small button--ghost"
          type="button"
          onClick={onRefresh}
          disabled={loading}
        >
          {loading ? "Atualizando..." : "Atualizar"}
        </button>
      </div>

      {loading && customers.length === 0 ? (
        <div className="empty-state">Carregando cadastros...</div>
      ) : customers.length === 0 ? (
        <div className="empty-state">
          <strong>Nenhum cliente cadastrado ainda.</strong>
          <span>Preencha o formulário para criar o primeiro registro.</span>
        </div>
      ) : (
        <div className="customer-list">
          {customers.map((customer) => (
            <article className="customer-item" key={customer.id}>
              <div className="customer-item__content">
                <div className="customer-item__main">
                  <strong>{customer.nome}</strong>
                  <span>{customer.email}</span>
                </div>

                <div className="customer-item__address">
                  <span>
                    {customer.logradouro}, {customer.numero}
                    {customer.complemento ? ` - ${customer.complemento}` : ""}
                  </span>
                  <span>
                    {customer.bairro} • {customer.cidade}/{customer.uf} • {customer.cep}
                  </span>
                </div>

                <small>Cadastrado em {formatDate(customer.created_at)}</small>
              </div>

              <div className="customer-item__actions" aria-label={`Ações para ${customer.nome}`}>
                <button
                  className="button button--small button--ghost"
                  type="button"
                  onClick={() => onEdit(customer)}
                  disabled={loading}
                  aria-label={`Editar cadastro de ${customer.nome}`}
                >
                  Editar
                </button>

                <button
                  className="button button--small button--danger"
                  type="button"
                  onClick={() => onDelete(customer)}
                  disabled={loading}
                  aria-label={`Excluir cadastro de ${customer.nome}`}
                >
                  Excluir
                </button>
              </div>
            </article>
          ))}
        </div>
      )}
    </section>
  );
}