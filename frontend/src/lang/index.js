import publicEn from "./public/en";
import publicUk from "./public/uk";

import adminEn from "./admin/en";
import adminUk from "./admin/uk";

export default {
  en: {
    ...publicEn,
    admin: adminEn,
  },
  uk: {
    ...publicUk,
    admin: adminUk,
  },
};
