<template>
  <div>coordonnées bancaires</div>
</template>
<script>
import Translations from "@translations/components/user/profile/BankAccount.json";

export default {
  i18n: {
    messages: Translations
  },
  props: {
    user: {
      type: Object,
      default: () => {}
    }
  },
  data () {
    return {
    }
  }
}
</script>