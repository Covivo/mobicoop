<template>
  <v-container class="pa-7">
    <v-expansion-panels
      accordion
      flat
      class="rounded-0"
    >
      <v-expansion-panel
        v-for="(panel, i) in getPanels"
        :key="i"
        class="m-panel"
      >
        <v-expansion-panel-header class="panel-header">
          <div>
            <v-icon
              v-if="panel.success()"
              color="primary"
            >
              mdi-check-circle
            </v-icon>
            <v-icon
              v-else-if="panel.error()"
              color="error"
            >
              mdi-close-circle
            </v-icon>
            <v-icon
              v-else
              color="primary"
            />
            <span class="ml-5 font-weight-medium">
              {{ panel.title }}
            </span>
          </div>
        </v-expansion-panel-header>
        <v-expansion-panel-content>
          <!-- The panel text content -->
          <v-row v-if="panel.success()">
            <v-col
              class="text-left"
              v-html="panel.texts.success"
            />
          </v-row>
          <v-row v-else-if="panel.error()">
            <v-col
              class="text-left"
              v-html="panel.texts.error"
            />
          </v-row>
          <v-row v-else>
            <v-col
              class="text-left"
              v-html="panel.texts.default"
            />
          </v-row>
          <!-- The panel actions -->
          <v-row v-if="panel.success()">
            <v-col
              v-for="(action,j) in panel.actions.success"
              :key="j"
              cols="12"
            >
              <a :href="action.href">{{ action.title }}</a>
            </v-col>
          </v-row>
          <v-row v-else-if="panel.error()">
            <v-col
              v-for="(action,j) in panel.actions.error"
              :key="j"
              cols="12"
            >
              <a :href="action.href">{{ action.title }}</a>
            </v-col>
          </v-row>
        </v-expansion-panel-content>
      </v-expansion-panel>
    </v-expansion-panels>
  </v-container>
</template>
<script>
import { isNull, merge } from "lodash";

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/EECIncentiveStatus/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/user/EECIncentiveStatus/";

import { eec_type_short, eec_type_long } from "@utils/constants";

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
  props: {
    type: {
      type: String,
      default: null
    },
    subscription: {
      type: Object,
      default: () => ({})
    },
    nbPendingProofs: {
      type: Number,
      default: 0
    },
    nbRejectedProofs: {
      type: Number,
      default: 0
    },
    nbValidatedProofs: {
      type: Number,
      default: 0
    },
  },
  data() {
    const _this = this;
    return  {
      panels: {
        subscribe: {
          title: this.$t('improvedIncentive.panels.subscribe.title'),
          error() {
            return !_this.subscription || (
              _this.subscription
              && _this.subscription.progression
              && !isNull(_this.subscription.progression.registrationFinalized) && true != _this.subscription.progression.registrationFinalized
            );
          },
          success() {
            return _this.subscription && _this.subscription.progression
              && true === _this.subscription.progression.registrationFinalized;
          },
          texts: {
            default: this.$t('improvedIncentive.panels.subscribe.texts.default'),
            success: this.$t('improvedIncentive.panels.subscribe.texts.success'),
            error: this.$t('improvedIncentive.panels.subscribe.texts.error')
          },
          actions: {
            success: [
              {
                active: true,
                title: this.$t('improvedIncentive.panels.subscribe.actions.success[0].title'),
                href: this.$t('improvedIncentive.panels.subscribe.actions.success[0].href')
              }
            ],
            error: []
          }
        },
        publish: {
          title: this.$t('improvedIncentive.panels.publish.title'),
          error() {
            return _this.subscription && _this.subscription.progression
              && !isNull(_this.subscription.progression.firstCarpoolPublished) && false === _this.subscription.progression.firstCarpoolPublished;
          },
          success() {
            return _this.subscription && _this.subscription.progression
              && true === _this.subscription.progression.firstCarpoolPublished;
          },
          texts: {
            default: this.$t('improvedIncentive.panels.publish.texts.default'),
            success: this.$t('improvedIncentive.panels.publish.texts.success'),
            error: this.$t('improvedIncentive.panels.publish.texts.error')
          },
          actions: {
            success: [
              {
                active: true,
                title: this.$t('improvedIncentive.panels.publish.actions.success[0].title'),
                href: this.$t('improvedIncentive.panels.publish.actions.success[0].href')
              }
            ],
            error: [
              {
                active: true,
                title: this.$t('improvedIncentive.panels.publish.actions.error[0].title'),
                href: this.$t('improvedIncentive.panels.publish.actions.error[0].href')
              }
            ]
          }
        },
        carpool: {
          title: this.$t('improvedIncentive.panels.carpool.title'),
          error() {
            return _this.subscription && _this.subscription.progression
              && !isNull(_this.subscription.progression.carpoolRegistered) && false === _this.subscription.progression.carpoolRegistered;
          },
          success() {
            return _this.subscription && _this.subscription.progression
              && true === _this.subscription.progression.carpoolRegistered;
          },
          texts: {
            default: this.$t('improvedIncentive.panels.carpool.texts.default'),
            success: this.$t('improvedIncentive.panels.carpool.texts.success'),
            error: this.$t('improvedIncentive.panels.carpool.texts.error')
          },
          actions: {
            success: [],
            error: [
              {
                active: true,
                title: this.$t('improvedIncentive.panels.carpool.actions.error[0].title'),
                href: this.$t('improvedIncentive.panels.carpool.actions.error[0].href')
              },
              {
                active: true,
                title: this.$t('improvedIncentive.panels.carpool.actions.error[1].title'),
                href: this.$t('improvedIncentive.panels.carpool.actions.error[1].href')
              },
              {
                active: true,
                title: this.$t('improvedIncentive.panels.carpool.actions.error[2].title'),
                href: this.$t('improvedIncentive.panels.carpool.actions.error[2].href')
              }
            ]
          }
        },
        payAndValidate: {
          title: this.$t('improvedIncentive.panels.payAndValidate.title'),
          error() {
            return _this.subscription && _this.subscription.progression
              && !isNull(_this.subscription.progression.carpoolPayedAndValidated) && false === _this.subscription.progression.carpoolPayedAndValidated;
          },
          success() {
            return _this.subscription && _this.subscription.progression
              && true === _this.subscription.progression.carpoolPayedAndValidated;
          },
          texts: {
            default: this.$t('improvedIncentive.panels.payAndValidate.texts.default'),
            success: this.$t('improvedIncentive.panels.payAndValidate.texts.success'),
            error: this.$t('improvedIncentive.panels.payAndValidate.texts.error')
          },
          actions: {
            success: [],
            error: [
              {
                active: true,
                title: this.$t('improvedIncentive.panels.payAndValidate.actions.error[0].title'),
                href: this.$t('improvedIncentive.panels.payAndValidate.actions.error[0].href')
              }
            ]
          }
        },
        validate: {
          title: this.$t('improvedIncentive.panels.validate.title'),
          error() {
            return _this.subscription && _this.subscription.progression
              && !isNull(_this.subscription.progression.carpoolValidated) && false === _this.subscription.progression.carpoolValidated;
          },
          success() {
            return _this.subscription && _this.subscription.progression
              && true === _this.subscription.progression.carpoolValidated;
          },
          texts: {
            default: this.$t('improvedIncentive.panels.validate.texts.default'),
            success: this.$t('improvedIncentive.panels.validate.texts.success'),
            error: this.$t('improvedIncentive.panels.validate.texts.error')
          },
          actions: {
            success: [],
            error: [
              {
                active: true,
                title: this.$t('improvedIncentive.panels.validate.actions.error[0].title'),
                href: this.$t('improvedIncentive.panels.validate.actions.error[0].href')
              }
            ]
          }
        },
        bonified: {
          title: this.$t('improvedIncentive.panels.receive.title'),
          error() {
            return _this.subscription && _this.subscription.progression
              && !isNull(_this.subscription.progression.subscriptionBonified) && false === _this.subscription.progression.subscriptionBonified;
          },
          success() {
            return _this.subscription && _this.subscription.progression
              && true === _this.subscription.progression.subscriptionBonified;
          },
          texts: {
            default: this.$t('improvedIncentive.panels.receive.texts.default'),
            success: this.$t('improvedIncentive.panels.receive.texts.success'),
            error: this.$t('improvedIncentive.panels.receive.texts.error')
          },
          actions: {
            success: [],
            error: []
          }
        },
      }
    }
  },
  computed: {
    getPanels() {
      return this.isLongType
        ? [
          this.panels.subscribe,
          this.panels.publish,
          this.panels.carpool,
          this.panels.payAndValidate,
          this.panels.bonified
        ]
        : [
          this.panels.subscribe,
          this.panels.carpool,
          this.panels.validate,
          this.panels.bonified
        ]
      ;
    },
    isShortType() {
      return this.type === eec_type_short;
    },
    isLongType() {
      return this.type === eec_type_long;
    },
  },
  methods: {
  }
}
</script>
<style lang="scss" scoped>
.m-panel {
  background-color: transparent;
  border-bottom: 1px solid;
}
.panel-header {
  height: 72px;
}
</style>
