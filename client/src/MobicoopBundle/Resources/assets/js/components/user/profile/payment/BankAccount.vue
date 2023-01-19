<template>
  <div>
    <div v-if="loading">
      <v-skeleton-loader
        ref="skeleton"
        type="list-item-avatar-three-line"
        class="mx-auto mt-2"
      />
    </div>
    <div v-else>
      <v-alert
        v-if="error"
        type="error"
      >
        {{ $t('error') }}
      </v-alert>
      <PaymentStatus :can-be-paid="canBePaid" />
      <v-form
        v-if="!bankCoordinates"
        v-model="valid"
        class="mt-0"
      >
        <v-container class="pa-0">
          <v-row justify="center">
            <v-col
              cols="12"
              md="10"
              class="text-center text-h6 pt-4"
            >
              <v-alert
                type="info"
                color="accent"
                class="text-left my-2"
                dense
              >
                {{ $t('textInfo') }}
              </v-alert>
              {{ $t('titleCoordinates') }}
            </v-col>
          </v-row>
          <v-row justify="center">
            <v-col
              cols="12"
              md="10"
            >
              <v-tooltip right>
                <template v-slot:activator="{ on }">
                  <v-text-field
                    v-model="form.iban"
                    :label="$t('form.label.iban')"
                    :rules="form.rules.ibanRules"
                    required
                    v-on="on"
                  />
                </template>
                <span>{{ $t('form.tooltip.iban') }}</span>
              </v-tooltip>
            </v-col>
          </v-row>
          <v-row justify="center">
            <v-col
              cols="12"
              md="10"
            >
              <v-text-field
                v-model="form.bic"
                :label="$t('form.label.bic')"
                :rules="form.rules.bicRules"
              />
            </v-col>
          </v-row>
          <v-row justify="center">
            <v-col
              cols="12"
              md="10"
              class="text-center text-h6 pt-4"
            >
              {{ $t('titleAddress') }}
            </v-col>
          </v-row>
          <v-row justify="center">
            <v-col
              cols="12"
              md="10"
              class="text-left pt-4 font-italic"
            >
              {{ $t('textAddress') }}
            </v-col>
          </v-row>
          <v-row justify="center">
            <v-col
              cols="12"
              md="10"
            >
              <geocomplete
                :uri="geoSearchUrl"
                :results-order="geoCompleteResultsOrder"
                :palette="geoCompletePalette"
                :chip="geoCompleteChip"
                :restrict="['housenumber','street']"
                :label="$t('form.label.address.check')"
                @address-selected="addressSelected"
              />
            </v-col>
          </v-row>
          <v-row
            v-if="addressValidation"
            justify="center"
          >
            <v-col
              cols="12"
              md="10"
              class="grey lighten-3"
            >
              <v-row justify="center">
                <v-col
                  cols="12"
                  md="10"
                  class="text-left pt-4 font-italic"
                >
                  {{ $t('textAddressDetails') }}
                </v-col>
              </v-row>
              <v-row>
                <v-col cols="3">
                  <v-text-field
                    v-model="form.addressDetail.houseNumber"
                    :label="$t('form.label.address.houseNumber')"
                    required
                  />
                </v-col>
                <v-col cols="9">
                  <v-text-field
                    v-model="form.addressDetail.street"
                    :label="$t('form.label.address.street')"
                    :rules="form.rules.streetRules"
                    required
                  />
                </v-col>
              </v-row>
              <v-row>
                <v-col cols="3">
                  <v-text-field
                    v-model="form.addressDetail.postalCode"
                    :label="$t('form.label.address.postalCode')"
                    :rules="form.rules.postalCodeRules"
                    required
                  />
                </v-col>
                <v-col cols="9">
                  <v-text-field
                    v-model="form.addressDetail.addressLocality"
                    :label="$t('form.label.address.addressLocality')"
                    :rules="form.rules.addressLocalityRules"
                    required
                  />
                </v-col>
              </v-row>
            </v-col>
          </v-row>
          <v-row justify="center">
            <v-col
              cols="12"
              md="10"
              class="text-center"
            >
              <v-btn
                rounded
                color="secondary"
                class="mt-4 justify-self-center"
                :disabled="!addressValidation || !valid"
                @click="addBankCoordinates"
              >
                {{ $t('register') }}
              </v-btn>
            </v-col>
          </v-row>
        </v-container>
      </v-form>
      <div v-else>
        <v-row justify="center">
          <v-col
            cols="12"
            md="10"
            class="text-center text-h6 pt-4"
          >
            {{ $t('titleAlreadyRegistered') }}
          </v-col>
        </v-row>
        <v-row justify="center">
          <v-col cols="10">
            <v-row>
              <v-col cols="8">
                <v-row>
                  <v-col cols="12">
                    <label class="caption">{{ $t('form.label.iban') }}</label> {{ bankCoordinates.iban }}
                  </v-col>
                </v-row>
                <v-row>
                  <v-col cols="12">
                    <label class="caption">{{ $t('form.label.bic') }}</label> {{ bankCoordinates.bic }}
                  </v-col>
                </v-row>
              </v-col>
              <v-col cols="2">
                <v-row align="center">
                  <v-col
                    cols="12"
                  >
                    <v-btn
                      class="secondary my-1"
                      icon
                      @click.stop="dialog = true"
                    >
                      <v-icon
                        class="white--text"
                      >
                        mdi-delete-outline
                      </v-icon>
                    </v-btn>
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12">
                <p>{{ bankCoordinates.address.houseNumber }} {{ bankCoordinates.address.street }}</p>
                <p>{{ bankCoordinates.address.postalCode }} {{ bankCoordinates.address.addressLocality }}</p>
              </v-col>
            </v-row>
          </v-col>
        </v-row>
      </div>
      <v-row justify="center">
        <v-col cols="10">
          <IdentityValidation
            :validation-docs-authorized-extensions="validationDocsAuthorizedExtensions"
            :payment-profile-status="(bankCoordinates) ? bankCoordinates.status : 0"
            :validation-status="(bankCoordinates) ? bankCoordinates.validationStatus : 0"
            :validation-asked-date="(bankCoordinates) ? bankCoordinates.validationAskedDate : null"
            :refusal-reason="(bankCoordinates) ? bankCoordinates.refusalReason : null"
            @identityDocumentSent="identityDocumentSent"
          />
        </v-col>
      </v-row>
    </div>

    <v-dialog
      v-model="dialog"
      max-width="400"
    >
      <v-card>
        <v-card-title class="headline">
          {{ $t('modalSuppr.title') }}
        </v-card-title>

        <v-card-text>
          <p>{{ $t('modalSuppr.line1') }}</p>
          <p>{{ $t('modalSuppr.line2') }}</p>
          <p>{{ $t('modalSuppr.line3') }}</p>
        </v-card-text>

        <v-card-actions>
          <v-spacer />

          <v-btn
            color="red darken-1"
            text
            @click="dialog = false"
          >
            {{ $t('modalSuppr.no') }}
          </v-btn>

          <v-btn
            color="green darken-1"
            text
            @click="deleteBankCoordinates()"
          >
            {{ $t('modalSuppr.yes') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
import maxios from "@utils/maxios";
import moment from "moment";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/profile/payment/BankAccount/";
import Geocomplete from "@components/utilities/geography/Geocomplete";
import PaymentStatus from "@js/components/user/profile/payment/PaymentStatus";
import IdentityValidation from "@js/components/user/profile/payment/IdentityValidation";

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
    Geocomplete,
    PaymentStatus,
    IdentityValidation
  },
  props: {
    user: {
      type: Object,
      default: () => {}
    },
    geoSearchUrl: {
      type: String,
      default: null
    },
    validationDocsAuthorizedExtensions: {
      type: String,
      default: null
    },
    geoCompleteResultsOrder: {
      type: Array,
      default: null
    },
    geoCompletePalette: {
      type: Object,
      default: () => ({})
    },
    geoCompleteChip: {
      type: Boolean,
      default: false
    },
  },
  data () {
    return {
      valid: false,
      form: {
        iban:"",
        bic:"",
        formAddress:null,
        addressDetail:{
          houseNumber:null,
          street:null,
          postalCode:null,
          addressLocality:null
        },
        rules:{
          ibanRules: [
            v => !!v || this.$t('form.errors.ibanRequired'),
            v => (/[a-zA-Z]{2}[0-9]{2}[a-zA-Z0-9]{4}[0-9]{7}([a-zA-Z0-9]?){0,16}/).test(v) || this.$t('form.errors.iban'),
          ],
          bicRules: [
            v => (/[a-zA-Z]{4}[a-zA-Z]{2}[a-zA-Z0-9]{2}([a-zA-Z0-9]{3})?|^$/).test(v) || this.$t('form.errors.bic'),
          ],
          streetRules: [
            v => !!v || this.$t('form.errors.streetRequired'),
          ],
          postalCodeRules: [
            v => !!v || this.$t('form.errors.postalCodeRequired'),
          ],
          addressLocalityRules: [
            v => !!v || this.$t('form.errors.addressLocalityRequired'),
          ],
        }
      },
      bankCoordinates: null,
      loading:false,
      title:this.$t('title'),
      dialog:false,
      error:false,
      addressValidation: false
    }
  },
  computed:{
    canBePaid(){
      if(!this.bankCoordinates || this.bankCoordinates.status == 0 || this.bankCoordinates.validationStatus == 0 || this.bankCoordinates.validationStatus > 1){
        return false;
      }
      return true;
    }
  },
  mounted(){
    this.getBankCoordinates();
  },
  methods:{
    getBankCoordinates(){
      this.loading = true;
      maxios.post(this.$t("uri.getCoordinates"))
        .then(response => {
          // console.error(response.data);
          if(response.data){
            if(response.data[0]) this.bankCoordinates = response.data[0];
            this.title = this.$t('titleAlreadyRegistered')
            this.loading = false;
          }
        })
        .catch(function (error) {
          console.error(error);
        });
    },
    deleteBankCoordinates(){
      this.dialog = false;
      this.loading = true;
      this.error = false;
      let params = {
        "bankAccountId":this.bankCoordinates.id
      }
      maxios.post(this.$t("uri.deleteCoordinates"),params)
        .then(response => {
          if(response.data.error){
            this.error = true;
          }
          else{
            this.bankCoordinates = null;
          }
          this.loading = false;
        })
        .catch(function (error) {
          console.error(error);
        })
    },
    addBankCoordinates(){
      this.loading = true;
      this.error = false;

      // We override several parameters retrieive by the SIG and validated directly by the user
      // We keep the ones given in the form
      this.form.formAddress.houseNumber = this.form.addressDetail.houseNumber;
      this.form.formAddress.street = this.form.addressDetail.street;
      this.form.formAddress.postalCode = this.form.addressDetail.postalCode;
      this.form.formAddress.addressLocality = this.form.addressDetail.addressLocality;

      let params = {
        "iban":this.form.iban,
        "bic":this.form.bic,
        "address":this.form.formAddress
      }
      maxios.post(this.$t("uri.addCoordinates"),params)
        .then(response => {
          if(response.data.error){
            this.error = true;
            this.loading = false;
          }
          else{
            this.getBankCoordinates();
          }
        })
        .catch(function (error) {
          console.error(error);
        })
    },
    addressSelected(address){
      this.form.formAddress = address;
      if(address){
        this.form.addressDetail.houseNumber = address.houseNumber;
        this.form.addressDetail.street = address.street;
        this.form.addressDetail.postalCode = address.postalCode;
        this.form.addressDetail.addressLocality = address.addressLocality;
      }
      this.addressValidation = true;
    },
    identityDocumentSent(data){
      if(!data.id){
        this.error = true;
      }
      else{
        this.bankCoordinates.validationAskedDate = moment();
      }
    }
  }
}
</script>
