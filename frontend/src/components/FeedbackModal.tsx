type FeedbackModalProps = {
  open: boolean;
  title: string;
  message: string;
  variant?: "success" | "error" | "info";
  onClose: () => void;
  onConfirm?: () => void;
  confirmLabel?: string;
  closeLabel?: string;
  confirming?: boolean;
};

export function FeedbackModal({
  open,
  title,
  message,
  variant = "info",
  onClose,
  onConfirm,
  confirmLabel = "Confirmar",
  closeLabel = "Entendi",
  confirming = false,
}: FeedbackModalProps) {
  if (!open) {
    return null;
  }

  const hasConfirmAction = typeof onConfirm === "function";

  return (
    <div className="modal-backdrop" role="presentation" onMouseDown={onClose}>
      <section
        className="modal-card"
        role="dialog"
        aria-modal="true"
        aria-labelledby="feedback-title"
        onMouseDown={(event) => event.stopPropagation()}
      >
        <span className={`modal-icon modal-icon--${variant}`} aria-hidden="true">
          {variant === "success" ? "✓" : variant === "error" ? "!" : "i"}
        </span>

        <h2 id="feedback-title">{title}</h2>
        <p>{message}</p>

        {hasConfirmAction ? (
          <div className="modal-actions">
            <button
              className="button button--ghost button--full"
              type="button"
              onClick={onClose}
              disabled={confirming}
              autoFocus
            >
              {closeLabel}
            </button>

            <button
              className="button button--danger button--full"
              type="button"
              onClick={onConfirm}
              disabled={confirming}
            >
              {confirming ? "Processando..." : confirmLabel}
            </button>
          </div>
        ) : (
          <button
            className="button button--primary button--full"
            type="button"
            onClick={onClose}
            autoFocus
          >
            {closeLabel}
          </button>
        )}
      </section>
    </div>
  );
}