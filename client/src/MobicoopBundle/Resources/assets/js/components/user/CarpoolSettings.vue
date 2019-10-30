<template>
  <v-container>
    <v-row
      justify="center"
    >
      <v-col
        cols="12"
        sm="6"
        md="4"
        align="center"
      >
        <v-snackbar
          v-model="alert.show"
          :color="(alert.type === 'error')?'error':'primary'"
          top
        >
          {{ alert.message }}
          <v-btn
            color="white"
            text
            @click="alert.show = false"
          >
            <v-icon>mdi-close-circle-outline</v-icon>
          </v-btn>
        </v-snackbar>
      </v-col>
    </v-row>
    <v-row
      justify="center"
    >
      <v-col
        v-for="(item, itemIndex) in $t('items')"
        :key="itemIndex"
      >
        <v-card>
          <v-card-title>
            {{ item.title }}
          </v-card-title>
          <v-card-text>
            <v-radio-group
              v-model="$data['form'][item.key]['value']"
              :mandatory="false"
            >
              <v-radio
                v-for="(radio, index) in item.radios"
                :key="index"
                :label="radio.label"
                :value="radio.value"
              />
            </v-radio-group>
            <v-text-field
              v-if="item.favorite"
              v-model="$data['form'][item.key]['favorite']"
              :label="item.favorite.label"
            />
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
    <v-row justify="center">
      <v-btn
        :loading="loading"
        color="success"
        rounded
        @click="updateCarpoolSettings()"
      >
        {{ $t('button.label') }}
      </v-btn>
    </v-row>
  </v-container>
</template>

<script>
import {merge} from "lodash";
import axios from "axios";
import Translations from "@translations/components/user/CarpoolSettings.js";
import ClientTranslations from "@clientTranslations/components/user/CarpoolSettings.js";

let MergedTranslations = merge(Translations, ClientTranslations);

export default {
  i18n: {
    messages: MergedTranslations
  },
  data () {
    return {
      loading: false,
      alert: {
        type: "success",
        show: false,
        message: ""
      },
      form: {
        smoke: {
          value: null
        },
        music: {
          value: null,
          favorite: ""
        },
        chat: {
          value: null,
          favorite: ""
        }
      }
    }
  },
  methods: {
    updateCarpoolSettings() {
      const self = this;
      this.resetAlert();
      this.loading = true;
      axios.put(this.$t('button.route'), {
        smoke: this.form.smoke.value,
        music: this.form.music.value,
        musicFavorites: this.form.music.favorite,
        chat: this.form.chat.value,
        chatFavorites: this.form.chat.favorite
      })
        .then(function (response) {
          console.log(response.data);
          if (response.data && response.data.message) {
            self.alert = {
              type: "success",
              message: self.$t(response.data.message)
            };
          }
        })
        .catch(function (error) {
          console.error(error.response);
          let messages = "";
          if (error.response.data && error.response.data.message) {
            messages = self.$t(error.response.data.message);
          }
          self.alert = {
            type: "error",
            message: messages
          };
        }).finally(function () {
          self.loading = false;
          if (self.alert.message.length > 0) {
            self.alert.show = true;
          }
        })
    },
    resetAlert() {
      this.alert = {
        type: "success",
        message: "",
        show: false
      }
    }
  }
}
</script>

<style scoped lang="scss">
.v-card__title {
    word-break: unset;
}
</style>