<template>
  <v-container fluid>
    <v-row justify="center">
      <v-col>
        <v-row>
          <v-col
            cols="8"
            class="font-weight-bold text-h5"
          >
            {{ $t("needCarpoolProofs") }}
          </v-col>
          <v-col
            cols="8"
            class="font-italic text-caption"
            v-html="$t('clickAndGetFile')"
          />
          <v-tooltip right>
            <template v-slot:activator="{ on }">
              <div v-on="disableExportButton && on">
                <v-btn
                  color="secondary"
                  rounded
                  :disabled="disableExportButton"
                  width="175px"
                  :loading="exportLoading"
                  @click="carpoolExportDialog = true"
                >
                  {{ $t("export") }}
                </v-btn>
              </div>
            </template>
            <span>{{ $t("tooltip") }}</span>
          </v-tooltip>
        </v-row>
        <MissingDataToPayElectronically
          v-if="user.missingDataToPayElectronically.length > 0 && paymentElectronicActive"
          :user="user"
        />
        <v-tabs
          centered
          grow
        >
          <v-tab>{{ $t("carpools.active") }}</v-tab>
          <v-tab-item>
            <CarpoolsByFrequency
              v-if="carpools && carpools.active"
              :chronological-sorted="false"
              :carpools="carpools.active"
              :payment-electronic-active="paymentElectronicActive"
              :user="user"
            />
          </v-tab-item>
          <v-tab>{{ $t("carpools.archived") }}</v-tab>
          <v-tab-item>
            <CarpoolsByFrequency
              v-if="carpools && carpools.archived"
              :chronological-sorted="true"
              :carpools="carpools.archived"
              :payment-electronic-active="paymentElectronicActive"
              :user="user"
            />
          </v-tab-item>
        </v-tabs>
      </v-col>
    </v-row>
    <v-dialog
      v-model="carpoolExportDialog"
      max-width="800"
      persistent
    >
      <v-card max-width="800">
        <v-toolbar color="primary">
          <v-toolbar-title class="white--text">
            {{ $t("dialog.title") }}
          </v-toolbar-title>
          <v-spacer />
          <v-btn
            color="white"
            icon
            @click="carpoolExportDialog = false"
          >
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-toolbar>

        <v-row
          align="center"
          dense
          class="ml-2 mr-10"
        >
          <v-col cols="1" />
          <v-col
            justify="center"
            cols="2"
          >
            <p align="center">
              {{ $t("dialog.label1") }}
            </p>
          </v-col>
          <v-col
            cols="3"
            justify="center"
            align="center"
          >
            <v-menu
              v-model="menu"
              :close-on-content-click="false"
              :nudge-right="40"
              offset-y
              min-width="auto"
            >
              <template v-slot:activator="{ on, attrs }">
                <v-text-field
                  v-model="fromDate"
                  prepend-icon="mdi-calendar"
                  readonly
                  v-bind="attrs"
                  v-on="on"
                />
              </template>
              <v-date-picker
                v-model="fromDate"
                @input="menu = false"
              />
            </v-menu>
          </v-col>
          <v-col
            justify="center"
            cols="2"
          >
            <p align="center">
              {{ $t("dialog.label2") }}
            </p>
          </v-col>
          <v-col
            cols="3"
            justify="center"
            align="center"
          >
            <v-menu
              v-model="menu2"
              :close-on-content-click="false"
              :nudge-right="40"
              offset-y
              min-width="auto"
            >
              <template v-slot:activator="{ on, attrs }">
                <v-text-field
                  v-model="toDate"
                  prepend-icon="mdi-calendar"
                  readonly
                  v-bind="attrs"
                  v-on="on"
                />
              </template>
              <v-date-picker
                v-model="toDate"
                @input="menu2 = false"
              />
            </v-menu>
          </v-col>
          <v-spacer />
        </v-row>

        <v-card-actions class="justify-center">
          <v-btn
            color="primary"
            rounded
            width="175px"
            :disabled="fromDate == null || toDate == null"
            @click="
              carpoolExportDialog = false;
              getExport();
            "
          >
            {{ $t("dialog.exportButtonLabel") }}
          </v-btn>
          <v-btn
            color="primary"
            rounded
            width="175px"
            :disabled="fromDate !== null || toDate !== null"
            @click="
              carpoolExportDialog = false;
              getExport();
            "
          >
            {{ $t("dialog.exportAllButtonLabel") }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>
<script>
import maxios from "@utils/maxios";
import {
  messages_en,
  messages_fr,
  messages_eu,
  messages_nl
} from "@translations/components/user/profile/carpool/AcceptedCarpools/";
import CarpoolsByFrequency from "@components/user/profile/carpool/CarpoolsByFrequency.vue";
import MissingDataToPayElectronically from "@components/user/profile/MissingDataToPayElectronically.vue";

export default {
  i18n: {
    messages: {
      en: messages_en,
      nl: messages_nl,
      fr: messages_fr,
      eu: messages_eu
    }
  },
  components: {
    CarpoolsByFrequency,
    MissingDataToPayElectronically
  },
  props: {
    carpools: {
      type: Object,
      default: () => {}
    },
    user: {
      type: Object,
      default: null
    },
    paymentElectronicActive: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      carpoolExportDialog: false,
      fromDate: null,
      toDate: null,
      menu: false,
      menu2: false,
      exportLoading: false
    };
  },
  computed: {
    disableExportButton() {
      return !this.carpools.active && !this.carpools.archived;
    }
  },
  methods: {
    getExport() {
      this.exportLoading = true;
      const _this = this;
      let params = {
        fromDate: this.fromDate,
        toDate: this.toDate
      };
      maxios
        .post(this.$t("exportUrl"), params)
        .then(res => {
          _this.fromDate = null;
          _this.toDate = null;
          _this.openFileDownload(res);
        })
        .catch(function(error) {
          _this.fromDate = null;
          _this.toDate = null;
          console.error(error);
        })
        .finally(function() {
          _this.exportLoading = false;
        });
    },
    openFileDownload(response) {
      const link = document.createElement("a");
      link.href = response.data;
      link.target = "_blank";
      document.body.appendChild(link);
      link.click();
    }
  }
};
</script>

<style scoped></style>
