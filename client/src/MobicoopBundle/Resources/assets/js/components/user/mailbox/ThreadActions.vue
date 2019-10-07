<template>
  <v-content />
</template>
<script>
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/user/mailbox/ThreadActions.json";

export default {
  i18n: {
    messages: Translations,
    sharedMessages: CommonTranslations
  }
}
</script>