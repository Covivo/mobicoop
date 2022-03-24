<template>
  <v-card v-if="displayed">
    <v-card-text
      class="text-subtitle-1 orange lighten-5" 
      v-html="$t('message')"
    />
  </v-card>
</template>
<script>
import is from "@utils/is";
import {messages_en, messages_fr} from "@translations/components/user/mailbox/ThreadWarning/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr,
    }
  },
  props: {
    threadedPosts:  {
      type: [Array, String],
      default: null
    }
  },
  data() {
    return {
      displayed: false
    };
  },
  watch: {
    threadedPosts: {
      immediate: true,
      handler(newVal) {
        if (!newVal) { return; }
        const _this = this;
        switch (true) {
        case newVal instanceof Array:
          newVal.forEach(element => {
            _this.messageContainsPhoneNumberOrEmail(element);
          });
          break;
        case newVal instanceof String:
          _this.messageContainsPhoneNumberOrEmail(newVal);
          break;
        }
      }
    }
  },
  methods: {
    messageContainsPhoneNumberOrEmail(text)
    {
      const result = is.phone(text) || is.email(text);

      if (result) {
        this.displayed = true;
      }

      return result;
    }
  },
}
</script>