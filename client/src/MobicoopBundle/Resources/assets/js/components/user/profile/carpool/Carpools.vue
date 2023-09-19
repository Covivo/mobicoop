<template>
  <v-container
    fluid
  >
    <v-row justify="center">
      <v-col>
        <v-row>
          <v-col
            cols="8"
            class="font-weight-bold text-h5"
          >
            {{ $t('needCarpoolProofs') }}
          </v-col>
          <v-col
            cols="8"
            class="font-italic text-caption"
          >
            {{ $t('clickAndGetFile') }}
          </v-col>
          <v-tooltip
            right
          >
            <template v-slot:activator="{ on }">
              <div
                v-on="disableExportButton && on"
              >
                <v-btn
                  color="secondary"
                  rounded
                  :disabled="disableExportButton"
                  width="175px"
                  @click="carpoolExportDialog = true"
                >
                  {{ $t('export') }}
                </v-btn>
              </div>
            </template>
            <span>{{ $t('tooltip') }}</span>
          </v-tooltip>
        </v-row>
        <v-tabs
          centered
          grow
        >
          <v-tab>{{ $t('carpools.active') }}</v-tab>
          <v-tab-item>
            <v-container v-if="inProgressPunctualCarpools && inProgressPunctualCarpools.length">
              <v-row
                v-for="ad in inProgressPunctualCarpools"
                :key="ad.id"
              >
                <v-col cols="12">
                  <Carpool
                    :ad="ad"
                    :user="user"
                    :payment-electronic-active="paymentElectronicActive"
                  />
                </v-col>
              </v-row>
            </v-container>
            <v-container v-if="inProgressRegularCarpools && inProgressRegularCarpools.length">
              <v-row>
                <v-col cols="12">
                  <h2 class="h4 secondary--text">
                    {{ $t('regular.title') }}
                  </h2>
                </v-col>
              </v-row>
              <v-row
                v-for="ad in inProgressRegularCarpools"
                :key="ad.id"
              >
                <v-col cols="12">
                  <Carpool
                    :ad="ad"
                    :is-archived="true"
                    :user="user"
                    :payment-electronic-active="paymentElectronicActive"
                  />
                </v-col>
              </v-row>
            </v-container>
          </v-tab-item>
          <v-tab>{{ $t('carpools.archived') }}</v-tab>
          <v-tab-item>
            <v-container v-if="archivedPunctualCarpools && archivedPunctualCarpools.length">
              <v-row
                v-for="ad in archivedPunctualCarpools"
                :key="ad.id"
              >
                <v-col cols="12">
                  <Carpool
                    :ad="ad"
                    :is-archived="true"
                    :user="user"
                    :payment-electronic-active="paymentElectronicActive"
                  />
                </v-col>
              </v-row>
            </v-container>
            <v-container v-if="archivedRegularCarpools && archivedRegularCarpools.length">
              <v-row>
                <v-col cols="12">
                  <h2 class="h4 secondary--text">
                    {{ $t('regular.title') }}
                  </h2>
                </v-col>
              </v-row>
              <v-row
                v-for="ad in archivedRegularCarpools"
                :key="ad.id"
              >
                <v-col cols="12">
                  <Carpool
                    :ad="ad"
                    :is-archived="true"
                    :user="user"
                    :payment-electronic-active="paymentElectronicActive"
                  />
                </v-col>
              </v-row>
            </v-container>
          </v-tab-item>
        </v-tabs>
      </v-col>
    </v-row>
    <v-dialog
      v-model="carpoolExportDialog"
      max-width="800"
      persistent
    >
      <v-card
        max-width="800"
      >
        <v-toolbar
          color="primary"
        >
          <v-toolbar-title class="white--text">
            {{ $t('dialog.title') }}
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
            <p
              align="center"
            >
              {{ $t('dialog.label1') }}
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
            <p
              align="center"
            >
              {{ $t('dialog.label2') }}
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
            @click="carpoolExportDialog = false; getExport();"
          >
            {{ $t('dialog.exportButtonLabel') }}
          </v-btn>
          <v-btn
            color="primary"
            rounded
            width="175px"
            :disabled="fromDate !== null || toDate !== null"
            @click="carpoolExportDialog = false; getExport();"
          >
            {{ $t('dialog.exportAllButtonLabel') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>
<script>

import maxios from "@utils/maxios";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/profile/carpool/AcceptedCarpools/";
import Carpool from "@components/user/profile/carpool/Carpool.vue";
import { regular, punctual } from "@utils/constants";


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
    Carpool
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
    },
  },
  data(){
    return {
      carpoolExportDialog: false,
      fromDate: null,
      toDate: null,
      menu: false,
      menu2: false,
      inProgressPunctualCarpools: [],
      inProgressRegularCarpools: [],
      archivedPunctualCarpools: [],
      archivedRegularCarpools: []
    }
  },
  computed: {
    disableExportButton() {
      return !this.carpools.active && !this.carpools.archived;
    }
  },
  watch: {
    carpools() {
      this.buildCarpools();
    }
  },
  mounted() {
    this.buildCarpools();
  },
  methods:{
    getExport(){
      let params = {
        'fromDate':this.fromDate,
        'toDate':this.toDate
      }
      maxios.post(this.$t("exportUrl"), params)
        .then(res => {
          this.fromDate = null;
          this.toDate = null;
          this.openFileDownload(res);
        })
        .catch(function (error) {
          this.fromDate = null;
          this.toDate = null;
          console.error(error);
        });
    },
    openFileDownload(response){
      const link = document.createElement('a');
      link.href = response.data;
      link.target = "_blank";
      document.body.appendChild(link);
      link.click();
    },
    buildCarpools() {
      if (this.carpools) {
        if (this.carpools.active && this.carpools.active.length) {
          this.inProgressPunctualCarpools = this.getTypedCarpools(this.carpools.active, punctual).reverse();
          this.inProgressRegularCarpools = this.getTypedCarpools(this.carpools.active, regular);
        }
        if (this.carpools.archived && this.carpools.archived.length) {
          this.archivedPunctualCarpools = this.getTypedCarpools(this.carpools.archived, punctual);
          this.archivedRegularCarpools = this.getTypedCarpools(this.carpools.archived, regular);
        }
      }
    },
    getTypedCarpools(carpools, type) {
      return punctual === type
        ? [...carpools]
          .filter(carpool => 1 === carpool.frequency)
          .sort((a, b) => this.sortCarpoolsByDate(a, b))
        : [...carpools]
          .filter(carpool => 2 === carpool.frequency)
      ;
    },
    sortCarpoolsByDate(a, b) {
      switch (true) {
      // Si a est conducteur et b passager
      case a.roleDriver && b.rolePassenger: return new Date(`${b.passengers[0].fromDate} ${b.passengers[0].startTime}`) - new Date(`${a.driver.fromDate} ${a.driver.startTime}`);

      // Si a est conducteur et b conducteur
      case a.roleDriver && b.roleDriver: return new Date(`${b.passengers[0].fromDate} ${b.passengers[0].startTime}`) - new Date(`${a.passengers[0].fromDate} ${a.passengers[0].startTime}`);

      // Si a est passager et b conducteur
      case a.rolePassenger && b.roleDriver: return new Date(`${b.passengers[0].fromDate} ${b.passengers[0].startTime}`) - new Date(`${a.driver.fromDate} ${a.driver.startTime}`);

      // si b est passager est b passager
      case a.rolePassenger && b.rolePassenger: return new Date(`${b.driver.fromDate} ${b.driver.startTime}`) - new Date(`${a.driver.fromDate} ${a.driver.startTime}`);

      default:
        // This use case should never happen
        break;
      }
    }
  }
}
</script>

<style scoped>

</style>
