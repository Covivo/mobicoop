<template>
  <div>
    <v-card
      flat
      color="grey lighten-4"
    >
      <v-card-title
        class="text-center"
      >
        {{ $t('title') }}
      </v-card-title>
      <v-card-text class="text-center">
        <h2
          class="mb-4"
        >
          {{ $t('subtitle') }}
        </h2>
        <p class="font-weight-bold">
          {{ $t('intro', {eecProvider: eecProvider}) }}
        </p>
        <p>
          <v-list class="text-left">
            <v-list-item>
              <v-list-item-content>
                <v-list-item-title>{{ $t('mandatory1') }}</v-list-item-title>
              </v-list-item-content>

              <v-list-item-icon>
                <v-icon :color="confirmedPhoneNumber ? 'green' : 'red'">
                  {{ confirmedPhoneNumber ? 'mdi-check' : 'mdi-close' }}
                </v-icon>
              </v-list-item-icon>
            </v-list-item>
            <v-list-item>
              <v-list-item-content>
                <v-list-item-title>{{ $t('mandatory2') }}</v-list-item-title>
              </v-list-item-content>

              <v-list-item-icon>
                <v-icon :color="drivingLicenceNumberFilled ? 'green' : 'red'">
                  {{ drivingLicenceNumberFilled ? 'mdi-check' : 'mdi-close' }}
                </v-icon>
              </v-list-item-icon>
            </v-list-item>
          </v-list>
        </p>
        <p
          class="text-justify"
          v-html="$t('intro2', {eecPlatform: eecPlatform})"
        />
        <p class="font-weight-bold">
          {{ $t('title_list', {eecPlatform: eecPlatform, eecProvider: eecProvider}) }}
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
  </div>
</template>

<script>
import { merge } from "lodash";
import SsoLogins from '@components/user/SsoLogins';
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/EECIncentiveStatus/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/user/EECIncentiveStatus/";

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
  components:{
    SsoLogins
  },
  props: {
    confirmedPhoneNumber:{
      type: Boolean,
      default: false
    },
    drivingLicenceNumberFilled:{
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      checkboxes: [],
      checkboxesAllChecked:false,
      eecPlatform: this.$t('EEC-platform'),
      eecProvider: this.$t('EEC-provider'),
      items: [this.$t('item1', {eecProvider: this.$t('EEC-provider')}),
        this.$t('item2', {eecProvider: this.$t('EEC-provider')}),
        this.$t('item3', {eecProvider: this.$t('EEC-provider')}),
        this.$t('item4', {eecProvider: this.$t('EEC-provider')}),
        this.$t('item5', {eecProvider: this.$t('EEC-provider')})
      ],


    }
  },
  computed:{
    canSubscribe(){
      return this.confirmedPhoneNumber && this.drivingLicenceNumberFilled && this.checkboxesAllChecked;
    }
  },
  watch:{
    checkboxes(){
      this.checkboxesAllChecked = false;
      if(this.checkboxes.length == this.items.length){
        this.checkboxesAllChecked = true;
      }
    },
    checkboxesAllChecked(){
      this.updateStore(this.canSubscribe);
    }
  },
  methods:{
    updateStore(status){
      this.$store.commit('sso/setSsoButtonsActiveStatus', {
        ssoId: this.$t('service'),
        status: status
      });
      this.$store.commit('sso/setRefreshActiveButtons', true);
    }
  },
};
</script>

