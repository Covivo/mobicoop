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
          v-model="snackbar"
          :color="(alert.type === 'error')?'error':'success'"
          top
        >
          {{ alert.message }}
          <v-btn
            color="white"
            text
            @click="snackbar = false"
          >
            <v-icon>mdi-close-circle-outline</v-icon>
          </v-btn>
        </v-snackbar>
      </v-col>
    </v-row>
    <v-row>
      <v-col
        v-for="(item, itemIndex) in $t('items')"
        :key="itemIndex"
        cols="4"
      >
        <v-card
          :height="cardHeight"
          class="pa-2"
          flat
        >
          <v-container fluid>
            <v-row no-gutters>
              <v-col class="cols-12 ma-2 text-center">
                <p class="mb-0 mt-2">
                  {{ item.title }}
                </p>
              </v-col>
            </v-row>
            <v-row no-gutters>
              <v-col class="cols-12 text-left">
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
                      color="secondary"
                    />
                  </v-radio-group>
                  <v-text-field
                    v-if="item.favorite"
                    v-model="$data['form'][item.key]['favorite']"
                    class="mt-0 pt-0"
                    :label="item.favorite.label"
                  />
                </v-card-text>
              </v-col>
            </v-row>
          </v-container>
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
import maxios from "@utils/maxios";
import {messages_en, messages_fr, messages_eu} from "@translations/components/user/profile/CarpoolSettings/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  props: {
    user: {
      type: Object,
      default: () => {}
    }
  },
  data () {
    return {
      loading: false,
      snackbar: false,
      cardHeight: '100%',
      alert: {
        type: "success",
        message: ""
      },
      form: {
        smoke: {
          // returned value is integer
          value: this.user && this.user.smoke !== null ? this.user.smoke : null 
        },
        music: {
          // returned value from bundle is boolean, so we have to check null, true or false to show correct value
          value: !this.user || this.user.music === null ? null : this.user.music ? 1 : 0,
          favorite: this.user && this.user.musicFavorites && this.user.musicFavorites.length > 0 ? this.user.musicFavorites : ""
        },
        chat: {
          // returned value from bundle is boolean, so we have to check null, true or false to show correct value
          value: !this.user || this.user.chat === null ? null : this.user.chat ? 1 : 0,
          favorite: this.user && this.user.chatFavorites && this.user.chatFavorites.length > 0 ? this.user.chatFavorites : ""
        }
      }
    }
  },
  methods: {
    updateCarpoolSettings() {
      const self = this;
      this.resetAlert();
      this.loading = true;
      maxios.put(this.$t('button.route'), {
        smoke: this.form.smoke.value,
        music: this.form.music.value,
        musicFavorites: this.form.music.favorite,
        chat: this.form.chat.value,
        chatFavorites: this.form.chat.favorite,
      })
        .then(function (response) {
          if (response.data && response.data.message) {
            self.alert = {
              type: "success",
              message: self.$t(response.data.message)
            };
          }
        })
        .catch(function (error) {
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
            self.snackbar = true;
          }
        })
    },
    resetAlert() {
      this.alert = {
        type: "success",
        message: ""
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