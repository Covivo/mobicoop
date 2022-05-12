<template>
  <v-container class="py-0">
    <v-snackbar
      v-model="snackbar"
      :color="(alert.type === 'error')?'error':(alert.type === 'warning') ? 'warning' : 'success'"
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

    <v-row :class="paused?'warning':''">
      <v-tooltip bottom>
        <template v-slot:activator="{ on }">
          <v-icon
            v-if="isDriver && !isPassenger"
            class="accent pa-1 px-3 white--text"
            v-on="on"
          >
            mdi-car
          </v-icon>
        </template>
        <span> {{ $t('ads.tooltips.driver') }} </span>
      </v-tooltip>
      <v-tooltip
        v-if="isPassenger && isDriver"
        bottom
      >
        <template v-slot:activator="{ on }">
          <v-icon
            v-if="isPassenger"
            class="secondary pa-1 px-3 white--text"
            v-on="on"
          >
            mdi-walk
          </v-icon>
          <v-icon
            v-if="isDriver"
            class="accent pa-1 px-3 white--text"
            v-on="on"
          >
            mdi-car
          </v-icon>
        </template>
        <span>{{ $t('ads.tooltips.driverOrPassenger') }}</span>
      </v-tooltip>
      <v-divider
        v-if="isDriver && isPassenger"
        vertical
      />
      <v-tooltip bottom>
        <template v-slot:activator="{ on }">
          <v-icon
            v-if="isPassenger && !isDriver"
            class="secondary pa-1 px-3 white--text"
            v-on="on"
          >
            mdi-walk
          </v-icon>
        </template>
        <span>{{ $t('ads.tooltips.passenger') }}</span>
      </v-tooltip>
      <v-spacer />
      <v-col
        v-if="!isCarpool"
        cols="7"
        class="text-center"
      >
        <p
          v-if="isPausable && !isArchived && paused && !loading"
          class="white--text font-weight-bold my-3"
        >
          {{ $t('pause.info') }}
        </p>
        <p
          v-if="isSolidaryExclusive"
          class="text-left font-weight-bold my-3"
        >
          {{ $t('solidary.exclusive') }}
        </p>
      </v-col>
      <v-col
        v-if="!isCarpool"
        cols="3"
        class="text-right"
      >
        <v-btn
          v-if="isPausable && !isArchived && paused"
          class="success my-1"
          icon
          :loading="loading"
          @click="pauseAd"
        >
          <v-icon class="white--text">
            mdi-play
          </v-icon>
        </v-btn>
        <v-btn
          class="secondary my-1"
          :class="isArchived ? 'mr-1' : ''"
          icon
          :loading="loading"
          @click="hasAcceptedAsk ? activeAcceptedAskDialog() : hasAsk ? activeAskDialog() : activeBaseDialog()"
        >
          <v-icon
            class="white--text"
          >
            mdi-delete-outline
          </v-icon>
        </v-btn>
        <v-btn
          v-if="!isArchived"
          class="secondary my-1"
          icon
          :loading="loading"
          @click="updateAd"
        >
          <v-icon class="white--text">
            mdi-pencil
          </v-icon>
        </v-btn>
        <v-btn
          v-if="isPausable && !isArchived && !paused"
          class="secondary my-1"
          icon
          :loading="loading"
          @click="pauseAd"
        >
          <v-icon class="white--text">
            mdi-pause
          </v-icon>
        </v-btn>
      </v-col>
      <v-col
        v-else-if="paymentStatus!==null"
        class="text-right"
      >
        <AdPayment
          :is-driver="isDriver"
          :is-passenger="isPassenger"
          :payment-status="paymentStatus"
          :payment-item-id="paymentItemId"
          :week="paymentWeek"
          :unpaid-date="unpaidDate"
          outlined
          show-unpaid
          :payment-electronic-active="paymentElectronicActive"
          @activePanel="activePanel()"
        />
      </v-col>
    </v-row>

    <!--DIALOG-->
    <v-row justify="center">
      <v-dialog
        v-model="dialogActive"
        persistent
        max-width="495"
      >
        <v-card>
          <v-card-title class="text-h5">
            {{ dialog.title }}
          </v-card-title>
          <v-card-text>
            <p>{{ dialog.content }}</p>
            <v-textarea
              v-if="dialog.textarea"
              v-model="deleteMessage"
            />
          </v-card-text>
          <v-card-actions>
            <v-spacer />
            <v-btn
              color="green darken-1"
              text
              :loading="loading"
              @click="dialogActive = false"
            >
              {{ $t("delete.dialog.cancel") }}
            </v-btn>
            <v-btn
              color="green darken-1"
              text
              :loading="loading"
              @click="deleteAd()"
            >
              {{ $t("delete.dialog.validate") }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-row>
  </v-container>
</template>

<script>
import maxios from "@utils/maxios";
import formData from "../../../../utils/request";
import AdPayment from '@components/user/profile/ad/AdPayment.vue';
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/profile/ad/AdHeader/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  components: {
    AdPayment
  },
  props: {
    isDriver: {
      type: Boolean,
      default: false
    },
    isPassenger: {
      type: Boolean,
      default: false
    },
    isPausable: {
      type: Boolean,
      default: true
    },
    isPaused: {
      type: Boolean,
      default: false
    },
    isArchived: {
      type: Boolean,
      default: false
    },
    isSolidaryExclusive: {
      type: Boolean,
      default: false
    },
    adId: {
      type: Number,
      default: null
    },
    adFrequency: {
      type: Number,
      default: null
    },
    hasAsk: {
      type: Boolean,
      default: false
    },
    hasAcceptedAsk: {
      type: Boolean,
      default: false
    },
    isCarpool: {
      type: Boolean,
      default: false
    },
    paymentStatus: {
      type: Number,
      default: null
    },
    paymentItemId: {
      type: Number,
      default: null
    },
    paymentWeek: {
      type: Number,
      default: null
    },
    unpaidDate: {
      type: String,
      default: null
    },
    paymentElectronicActive: {
      type: Boolean,
      default: false
    },
  },
  data () {
    return {
      loading: false,
      snackbar: false,
      alert: {
        type: "success",
        message: ""
      },
      dialogActive: false,
      dialog: {
        title: "",
        content: "",
        textarea: true
      },
      deleteMessage: "",
      paused: this.isPaused
    }
  },
  methods: {
    deleteAd () {
      this.resetAlert();
      const self = this;
      this.loading = true;
      maxios.delete(this.$t('delete.route'), {
        data: {
          adId: this.adId,
          deletionMessage: this.deleteMessage
        }
      })
        .then(function (response) {
          self.$emit('ad-deleted', self.isArchived, self.adId, self.$t("delete.success"));
        })
        .catch(function (error) {
          self.alert = {
            type: "error",
            message: self.$t("delete.error")
          };
        })
        .finally(function () {
          if (self.alert.message.length > 0) {
            self.snackbar = true;
          }
          self.loading = false;
          self.dialogActive = false;
        })
    },
    updateAd () {
      formData(this.$t('update.route', {id : this.adId}), null, 'GET');
    },
    pauseAd () {
      this.paused = !this.paused;
      this.loading = true;
      let ad = {
        id: this.adId,
        paused: this.paused
      };
      maxios
        .put(this.$t('update.route', {id : this.adId}), ad,
          {
            headers:{
              'content-type': 'application/json'
            }
          })
        .then(res => {
          console.error(res.data);
          if (res.data && res.data.message == 'error') {
            this.alert = {
              type: "warning",
              message: this.$t("pause.error.antifraud")
            };
            this.paused = !this.paused;
          }
          else if (res.data && res.data.result.id) {
            this.alert = {
              type: "success",
              message: res.data.result.paused ? this.$t("pause.success.pause") : this.$t("pause.success.unpause")
            };
            this.$emit('pause-ad', res.data.result.paused);
          } else {
            this.alert = {
              type: "error",
              message: this.paused ? this.$t("pause.error.pause") : this.$t("pause.error.unpause")
            };
            this.paused = !this.paused;
          }
          this.snackbar = true;
          this.loading = false;
        });
    },
    resetAlert() {
      this.alert = {
        type: "success",
        message: ""
      }
    },
    activeBaseDialog () {
      this.deleteMessage = "";
      this.dialog = {
        title: this.$t('delete.dialog.base.title'),
        content: this.$t('delete.dialog.base.text'),
        textarea: false
      };
      this.dialogActive = true;
    },
    activeAskDialog () {
      this.deleteMessage = "";
      this.dialog = {
        title: this.$t('delete.dialog.pending.title'),
        content: this.$t('delete.dialog.pending.text'),
        textarea: true
      };
      this.dialogActive = true;
    },
    activeAcceptedAskDialog () {
      this.deleteMessage = "";
      this.dialog = {
        title: this.$t('delete.dialog.accepted.title'),
        content: this.$t('delete.dialog.accepted.text'),
        textarea: true
      };
      this.dialogActive = true;
    },
    activePanel() {
      this.$emit('activePanel');
    }
  },
}
</script>

<style scoped>

</style>
