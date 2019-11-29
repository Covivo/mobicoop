<template>
  <v-container class="py-0">
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

    <v-row
      class="primary darken-2"
    >
      <v-tooltip bottom>
        <template v-slot:activator="{ on }">
          <v-icon
            v-if="isDriver && !(isDriver && isPassenger)"
            class="accent darken-2 pa-1 px-3 white--text"
            v-on="on"
          >
            mdi-car
          </v-icon>
        </template>
        <span> {{ $t('proposals.driver') }} </span>
      </v-tooltip>
      <v-tooltip
        v-if="isPassenger && isDriver"
        bottom
      >
        <template v-slot:activator="{ on }">
          <v-icon
            v-if="isPassenger"
            class="secondary darken-2 pa-1 px-3 white--text"
            v-on="on"
          >
            mdi-walk
          </v-icon>
          <v-icon
            v-if="isDriver"
            class="accent darken-2 pa-1 px-3 white--text"
            v-on="on"
          >
            mdi-car
          </v-icon>
        </template>
        <span>{{ $t('proposals.diverOrPassenger') }}</span>
      </v-tooltip>
      <v-divider
        v-if="isDriver && isPassenger"
        vertical
      />
      <v-tooltip bottom>
        <template v-slot:activator="{ on }">
          <v-icon
            v-if="isPassenger && !(isDriver && isPassenger)"
            class="secondary darken-2 pa-1 px-3 white--text"
            v-on="on"
          >
            mdi-walk
          </v-icon>
        </template>
        <span>{{ $t('proposals.passenger') }}</span>
      </v-tooltip>
      <v-spacer />
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
        class="secondary ma-1"
        icon
        :loading="loading"
      >
        <v-icon class="white--text">
          mdi-pencil
        </v-icon>
      </v-btn>
      <v-btn
        v-if="isPausable && !isArchived"
        class="secondary my-1 mr-1"
        icon
        :loading="loading"
      >
        <v-icon class="white--text">
          mdi-pause
        </v-icon>
      </v-btn>
    </v-row>

    <!--DIALOG-->
    <v-row justify="center">
      <v-dialog
        v-model="dialogActive"
        persistent
        max-width="500"
      >
        <v-card>
          <v-card-title class="headline">
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
              @click="deleteProposal()"
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
import { merge } from "lodash";
import axios from "axios";

import Translations from "@translations/components/user/profile/proposal/MyProposals.js";
import TranslationsClient from "@clientTranslations/components/user/profile/proposal/MyProposals.js";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged
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
    isArchived: {
      type: Boolean,
      default: false
    },
    proposalId: {
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
    }
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
      deleteMessage: ""
    }
  },
  methods: {
    deleteProposal () {
      this.resetAlert();
      const self = this;
      this.loading = true;
      axios.delete(this.$t('delete.route'), {
        data: {
          proposalId: this.proposalId,
          deletionMessage: this.deleteMessage
        }
      })
        .then(function (response) {
          if (response.data && response.data.message) {
            self.alert = {
              type: "success",
              message: self.$t(response.data.message)
            };
            self.$emit('proposal-deleted', self.proposalId);
            window.location.reload();
          }
        })
        .catch(function (error) {
          if (error.response.data && error.response.data.message) {
            self.alert = {
              type: "error",
              message: self.$t(error.response.data.message)
            };
          }
        })
        .finally(function () {
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
    }
  }
}
</script>

<style scoped>

</style>