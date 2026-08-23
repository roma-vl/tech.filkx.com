export default {
  title: "Надіслати масове сповіщення",
  description:
    "Надсилання ручних сповіщень всім користувачам або окремим групам.",
  success_queued: "Сповіщення успішно додано в чергу!",
  form: {
    title: "Заголовок",
    message: "Повідомлення",
    type: "Тип",
    recipients: "Отримувачі",
    user_ids: "ID користувачів (через кому)",
    action_url: "URL дії (необов'язково)",
    submit: "Надіслати",
    placeholders: {
      title: "напр. Технічні роботи",
      message: "Введіть текст сповіщення...",
      user_ids: "напр. 1, 5, 23",
      action_url: "https://...",
    },
  },
  types: {
    info: "Інфо",
    success: "Успіх",
    warning: "Попередження",
    error: "Помилка",
    fixed: "Фіксована сума",
    percent: "Відсоток",
  },
  recipients: {
    all: "Всі користувачі",
    selected: "Вибрані ID",
  },
};
