<template>
  <div>
    <v-card
      flat
      color="grey lighten-4"
    >
      <v-card-title class="text-center">
        {{ $t('title') }}
      </v-card-title>
      <v-card-text class="text-center">
        <h2 class="mb-4">
          {{ $t('subtitle') }}
        </h2>
        <p class="font-weight-bold">
          {{ $t('intro', { eecProvider: eecProvider }) }}
        </p>
        <p>
          <v-list class="text-left">
            <v-list-item>
              <v-list-item-content>
                <v-list-item-title>
                  <a @click="$vuetify.goTo('#phone-number', scrollOptions)">{{ $t('mandatory1') }}</a>
                </v-list-item-title>
              </v-list-item-content>

              <v-list-item-icon>
                <v-icon :color="confirmedPhoneNumber ? 'green' : 'red'">
                  {{ confirmedPhoneNumber ? 'mdi-check' : 'mdi-close' }}
                </v-icon>
              </v-list-item-icon>
            </v-list-item>
            <v-list-item>
              <v-list-item-content>
                <v-list-item-title>
                  <v-badge
                    color="secondary"
                    offset-x="-2.5"
                    offset-y="5"
                  >
                    <template #badge>
                      <div
                        style="cursor: pointer"
                        @click="tutorialDialog = true"
                      >
                        <v-icon>mdi-information-variant</v-icon>
                      </div>
                    </template>
                    <a @click="$vuetify.goTo('#driving-licence-number', scrollOptions)">
                      {{ $t('mandatory2') }}
                    </a>
                  </v-badge>
                </v-list-item-title>
              </v-list-item-content>

              <v-list-item-icon>
                <v-icon :color="drivingLicenceNumberFilled ? 'green' : 'red'">
                  {{ drivingLicenceNumberFilled ? 'mdi-check' : 'mdi-close' }}
                </v-icon>
              </v-list-item-icon>
            </v-list-item>
            <v-list-item v-if="eligibility">
              <v-list-item-content>
                <v-list-item-title>
                  <a @click="$vuetify.goTo('#user-postalAddress', scrollOptions)">{{ $t('mandatory3') }}</a>
                </v-list-item-title>
              </v-list-item-content>

              <v-list-item-icon>
                <v-icon :color="eligibility.addressFullyCompleted ? 'green' : 'red'">
                  {{ eligibility.addressFullyCompleted ? 'mdi-check' : 'mdi-close' }}
                </v-icon>
              </v-list-item-icon>
            </v-list-item>
          </v-list>
        </p>
        <p
          class="text-justify"
          v-html="$t('intro2', { eecPlatform: platform })"
        />
        <p class="font-weight-bold">
          {{ $t('title_list', { eecPlatform: platform, eecProvider: eecProvider }) }}
        </p>
        <p
          v-for="(item, i) in items"
          :key="i"
        >
          <v-checkbox
            v-model="checkboxes"
            :label="item"
            :value="i"
          />
        </p>
        <v-alert
          v-if="alert.text"
          border="left"
          colored-border
          type="warning"
          elevation="1"
          class="text-left mx-8"
        >
          <span
            class="text-left"
            v-html="alert.text"
          />
          <br>
          <span>{{ $t('EEC-eligibility.alert.cannotReApply') }}</span>
        </v-alert>
        <SsoLogins
          class="mt-5"
          :specific-service="$t('service')"
          :specific-path="$t('specificPath')"
          :default-buttons-active="false"
        />
        <p>
          <v-row align="center">
            <v-col
              cols="3"
              offset="3"
              class="text-right"
            >
              et identifiez-vous avec
            </v-col>
            <v-col
              cols="2"
              class="text-left"
            >
              <v-img
                contain
                :src="$t('logo-france-connect')"
              />
            </v-col>
          </v-row>
        </p>
        <p style="color: grey; font-size: 12px;">
          <v-row align="center">
            <v-col
              cols="3"
              class="text-right"
            >
              *{{ $t('EEC-provider-siren') }}
            </v-col>
          </v-row>
        </p>
      </v-card-text>
    </v-card>
    <!-- Dialog Tutorial -->
    <v-dialog
      v-model="tutorialDialog"
      width="50%"
    >
      <v-card>
        <v-card-title class="text-h5 grey lighten-2">
          {{ $t('dialogs.tutorial.title') }}
        </v-card-title>
        <v-card-text>
          <ul class="mt-5">
            <li v-html="$t('dialogs.tutorial.item-1', { apiUri: apiUri })" />
            <li v-html="$t('dialogs.tutorial.item-2', { apiUri: apiUri })" />
          </ul>
        </v-card-text>

        <v-divider />

        <v-card-actions>
          <v-spacer />
          <v-btn
            color="primary"
            text
            @click="tutorialDialog = false"
          >
            {{ $t('dialogs.tutorial.close-btn.text') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <!-- / Dialog Tutorial -->
  </div>
</template>

<script>
import { merge } from "lodash";
import maxios from "@utils/maxios";
import SsoLogins from '@components/user/SsoLogins';
import { messages_en, messages_fr, messages_eu, messages_nl } from "@translations/components/user/EECIncentiveStatus/";
import { messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl } from "@clientTranslations/components/user/EECIncentiveStatus/";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedNl = merge(messages_nl, messages_client_nl);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
let MessagesMergedEu = merge(messages_eu, messages_client_eu);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'nl': MessagesMergedNl,
      'fr': MessagesMergedFr,
      'eu': MessagesMergedEu
    }
  },
  components: {
    SsoLogins
  },
  props: {
    confirmedPhoneNumber: {
      type: Boolean,
      default: false
    },
    drivingLicenceNumberFilled: {
      type: Boolean,
      default: false
    },
    apiUri: {
      type: String,
      default: null
    },
    platform: {
      type: String,
      default: ""
    },
  },
  data() {
    return {
      checkboxes: [],
      checkboxesAllChecked: false,
      eecPlatform: this.$t('EEC-platform'),
      eecProvider: this.$t('EEC-provider'),
      items: [this.$t('item1', { eecProvider: this.$t('EEC-provider') }),
        this.$t('item2', { eecProvider: this.$t('EEC-provider') }),
        this.$t('item3', { eecProvider: this.$t('EEC-provider') }),
        this.$t('item4', { eecProvider: this.$t('EEC-provider') }),
        this.$t('item5', { eecProvider: this.$t('EEC-provider') })
      ],
      eligibility: null,
      loading: false,
      alert: {
        text: null
      },
      tutorialDialog: false,
    }
  },
  computed: {
    canSubscribe() {
      return this.confirmedPhoneNumber
        && this.drivingLicenceNumberFilled
        && this.checkboxesAllChecked
        && this.eligibility.longDistanceEligibility
        && this.eligibility.shortDistanceEligibility
        && this.eligibility.addressFullyCompleted
      ;
    },
    scrollOptions() {
      return {
        duration: 500,
        offset: 100,
        easing: 'easeInOutCubic',
      }
    },
  },
  watch: {
    checkboxes() {
      this.checkboxesAllChecked = false;
      if (this.checkboxes.length == this.items.length) {
        this.checkboxesAllChecked = true;
      }
    },
    drivingLicenceNumberFilled(n) {
      if (n) {
        this.updateStore(this.canSubscribe);
      }
    },
    checkboxesAllChecked() {
      this.updateStore(this.canSubscribe);
    }
  },
  mounted() {
    this.verifyEECSubscriptionEligibility();
  },
  methods: {
    updateStore(status) {
      this.$store.commit('sso/setSsoButtonsActiveStatus', {
        ssoId: this.$t('service'),
        status: status
      });
      this.$store.commit('sso/setRefreshActiveButtons', true);
    },
    verifyEECSubscriptionEligibility() {
      this.loading = true;
      maxios.get(this.$t("routes.getMyEecEligibility"))
        .then(res => {
          this.eligibility = res.data;
          this.loading = false;

          // TODO distinguer le type d'erreur

          switch (true) {
          // Ineligibility for long distance journeys
          case !this.eligibility.longDistanceEligibility && this.eligibility.longDistanceJourneysNumber > 0:
            this.alert.text = this.$t('EEC-eligibility.alert.LD-journeys');
            break;
            // Ineligibility for long distance phone doublon
          case !this.eligibility.longDistanceEligibility && this.eligibility.longDistancePhoneDoublon > 0:
            this.alert.text = this.$t('EEC-eligibility.alert.LD-phone');
            break;
            // Ineligibility for long distance driving licence doublon
          case !this.eligibility.longDistanceEligibility && this.eligibility.longDistanceDrivingLicenceNumberDoublon > 0:
            this.alert.text = this.$t('EEC-eligibility.alert.LD-driving-licence-number');
            break;
            // Ineligibility for short distance journeys
          case !this.eligibility.shortDistanceEligibility && this.eligibility.shortDistanceJourneysNumber > 0:
            this.alert.text = this.$t('EEC-eligibility.alert.CD-journeys');
            break;
            // Ineligibility for short distance phone doublon
          case !this.eligibility.shortDistanceEligibility && this.eligibility.shortDistancePhoneDoublon > 0:
            this.alert.text = this.$t('EEC-eligibility.alert.CD-phone');
            break;
            // Ineligibility for short distance driving licence doublon
          case !this.eligibility.shortDistanceEligibility && this.eligibility.shortDistanceDrivingLicenceNumberDoublon > 0:
            this.alert.text = this.$t('EEC-eligibility.alert.CD-driving-licence-number');
            break;
          }
        })
        .catch(function (error) {

        });
    }
  },
};
</script>
