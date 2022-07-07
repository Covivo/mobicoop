<template>
  <div>
    <v-card
      v-if="fraudWarningDisplay || displayed"
      color="info"
      flat
      dark
      max-height="50px"
      rounded="0"
    >
      <v-card-text>
        <v-icon
          left
        >
          mdi-alert
        </v-icon>
        <span class="white--text ">
          {{ $t('fraudWarningText.title') }}
        </span>
      </v-card-text>
    </v-card>
    <v-card
      v-if="fraudWarningDisplay || displayed"
      rounded="0"
      flat
    >
      <v-card-text>
        {{ $t('fraudWarningText.part1') }} <a
          :href="$t('fraudWarningText.link')"
          target="_blank"
        >{{ $t('fraudWarningText.textLink') }}</a>.
      </v-card-text>
    </v-card>
  </div>
</template>
<script>
import is from "@utils/is";
import {messages_en, messages_fr, message_eu, message_nl} from "@translations/components/utilities/WarningMessage";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr,
      'eu': message_eu,
      'nl': message_nl
    }
  },
  props: {
    fraudWarningDisplay: {
      type: Boolean,
      default: false
    },
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
