<template>
  <v-container>
    <v-row justify="center">
      <v-col align="center">
        <h1 v-if="regular">
          {{ $t('titleRegular') }}
        </h1>
        <h1 v-else>
          {{ $t('titleOccasional') }}
        </h1>
      </v-col>
    </v-row>

    <v-row>
      <v-col>
        <v-btn
          rounded
          color="secondary"
          :href="$t('buttons.back.route')"
        >
          <v-icon class="ml-n2">
            mdi-menu-left
          </v-icon>
          {{ $t('buttons.back.label') }}
        </v-btn>
      </v-col>
    </v-row>
    <!-- select period if regular -->
    <v-row
      v-if="regular"
      justify="center"
      align="center"
    >
      <v-col
        align="center"
        cols="2"
        class="mt-4 mr-n6 text-h4 primary--text "
      >
        <p>
          {{ $t('select.label') }}
        </p>
      </v-col>
      <v-col
        class="d-flex"
        cols="4"
      >
        <v-select
          :items="periods"
          :label="$t('select.label')"
        />
      </v-col>
    </v-row>
    
    <v-row justify="center"> 
      <!-- journey to pay or to validate -->
      <v-col
        cols="8"
        align="center"
      >
        <v-row>
          <!-- previous journey -->
          <v-col
            cols="2"
          >
            <v-card
              v-if="previousPaymentItem"
              raised
              height="950"
              class="mx-auto"
            >
              <v-row
                justify="center"
                class="pt-5"
              >
                <v-avatar size="75">
                  <img
                    v-if="previousPaymentItem.avatar"
                    :src="previousPaymentItem.avatar"
                  >
                  <img
                    v-else
                    src="/images/avatarsDefault/square_250.svg"
                  >
                </v-avatar>
              </v-row>
              <v-row justify="center">
                <v-card-title>
                  <p class="text-body-2">
                    {{ previousPaymentItem.givenName }} {{ previousPaymentItem.shortFamilyName }}.
                  </p>
                </v-card-title>
              </v-row>
            </v-card>
          </v-col>
          <!-- selected journey -->
          <v-col
            cols="8"
            align="center"
          >
            <v-card
              raised
              class="mx-auto"
              height="950"
            >
              <v-row
                justify="center"
                class="pt-5"
              >
                <v-avatar size="125">
                  <img
                    src="/images/avatarsDefault/square_250.svg"
                  >
                </v-avatar>
              </v-row>
              <v-row justify="center">
                <v-card-title>
                  <p>
                    {{ selectedPaymentItem.givenName }} {{ selectedPaymentItem.shortFamilyName }}.
                  </p>
                </v-card-title>
              </v-row>
              <v-row justify="center">
                <v-card-text>
                  <!-- dates -->
                  <v-row justify="center">
                    <p class="font-weight-bold">
                      {{ date }}
                    </p>
                  </v-row>
                  <!-- journey -->
                  <v-row
                    justify="center"
                  >
                    <v-col>
                      <p class="font-weight-bold">
                        {{ selectedPaymentItem.origin.addressLocality }}
                      </p>
                      <p>
                        {{ selectedPaymentItem.origin.street }}
                      </p>
                    </v-col>
                    <v-col>
                      <v-icon
                        size="60"
                        color="accent"
                      >
                        mdi-ray-start-end
                      </v-icon>
                    </v-col>
                    <v-col>
                      <p class="font-weight-bold">
                        {{ selectedPaymentItem.destination.addressLocality }}
                      </p>
                      <p>
                        {{ selectedPaymentItem.destination.street }}
                      </p>
                    </v-col>
                  </v-row>
                  <!-- if regular-->
                  <v-row v-if="regular">
                    <v-row
                      justify="center"
                      class="mt-6"
                    >
                      <p>{{ $t('regularInfo', {driver: selectedPaymentItem.givenName +' '+ selectedPaymentItem.shortFamilyName}) }}</p>
                    </v-row>
                    <v-row
                      justify="center"
                    >
                      <v-col
                        cols="3"
                        class="accent--text mt-3"
                      >
                        {{ $t('outward') }}
                        <v-icon color="accent">
                          mdi-arrow-right-bold
                        </v-icon>
                      </v-col>
                      <v-col
                        justify="center"
                      >
                        <day-list-chips 
                          :disabled="selectedPaymentItem.paymentDisabled || disabledComponent"
                          :is-outward="true"
                          :mon-active="selectedPaymentItem.outwardDays[0]['status'] == 1 ? true : false"
                          :tue-active="selectedPaymentItem.outwardDays[1]['status'] == 1 ? true : false"
                          :wed-active="selectedPaymentItem.outwardDays[2]['status'] == 1 ? true : false"
                          :thu-active="selectedPaymentItem.outwardDays[3]['status'] == 1 ? true : false"
                          :fri-active="selectedPaymentItem.outwardDays[4]['status'] == 1 ? true : false"
                          :sat-active="selectedPaymentItem.outwardDays[5]['status'] == 1 ? true : false"
                          :sun-active="selectedPaymentItem.outwardDays[6]['status'] == 1 ? true : false"
                          :mon-disabled="selectedPaymentItem.outwardDays[0]['status'] == 0 ? true : false"
                          :tue-disabled="selectedPaymentItem.outwardDays[1]['status'] == 0 ? true : false"
                          :wed-disabled="selectedPaymentItem.outwardDays[2]['status'] == 0 ? true : false"
                          :thu-disabled="selectedPaymentItem.outwardDays[3]['status'] == 0 ? true : false"
                          :fri-disabled="selectedPaymentItem.outwardDays[4]['status'] == 0 ? true : false"
                          :sat-disabled="selectedPaymentItem.outwardDays[5]['status'] == 0 ? true : false"
                          :sun-disabled="selectedPaymentItem.outwardDays[6]['status'] == 0 ? true : false"
                          @change="updateDaysList"
                        />
                      </v-col>
                    </v-row>
                    <v-row
                      v-if="selectedPaymentItem.returnDays"
                      justify="center"
                    >
                      <v-col
                        cols="3"
                        class="accent--text mt-3"
                      >
                        {{ $t('return') }}
                        <v-icon color="accent">
                          mdi-arrow-left-bold
                        </v-icon>
                      </v-col>
                      <v-col
                       
                        justify="center"
                      >
                        <day-list-chips
                          :is-outward="false"
                          :disabled="selectedPaymentItem.paymentDisabled || disabledComponent"
                          :mon-active="selectedPaymentItem.returnDays[0]['status'] == 1 ? true : false"
                          :tue-active="selectedPaymentItem.returnDays[1]['status'] == 1 ? true : false"
                          :wed-active="selectedPaymentItem.returnDays[2]['status'] == 1 ? true : false"
                          :thu-active="selectedPaymentItem.returnDays[3]['status'] == 1 ? true : false"
                          :fri-active="selectedPaymentItem.returnDays[4]['status'] == 1 ? true : false"
                          :sat-active="selectedPaymentItem.returnDays[5]['status'] == 1 ? true : false"
                          :sun-active="selectedPaymentItem.returnDays[6]['status'] == 1 ? true : false"
                          :mon-disabled="selectedPaymentItem.returnDays[0]['status'] == 0 ? true : false"
                          :tue-disabled="selectedPaymentItem.returnDays[1]['status'] == 0 ? true : false"
                          :wed-disabled="selectedPaymentItem.returnDays[2]['status'] == 0 ? true : false"
                          :thu-disabled="selectedPaymentItem.returnDays[3]['status'] == 0 ? true : false"
                          :fri-disabled="selectedPaymentItem.returnDays[4]['status'] == 0 ? true : false"
                          :sat-disabled="selectedPaymentItem.returnDays[5]['status'] == 0 ? true : false"
                          :sun-disabled="selectedPaymentItem.returnDays[6]['status'] == 0 ? true : false"
                          @change="updateDaysList"
                        />
                      </v-col>
                    </v-row>
                  </v-row>

                  <!-- if payement -->
                  <v-row v-if="isPayment">
                    <v-col>
                      <v-row
                        justify="center"
                        class="mt-4"
                      >
                        <v-col v-if="priceTravel">
                          <p>
                            {{ $t('price', {price: priceTravel}) }}
                          </p>
                        </v-col>
                      </v-row>
                      <v-row
                        v-if="displayElectronicPayment"
                        justify="center"
                      >
                        <v-radio-group
                          v-model="modeOfPayment"
                          column
                        >
                          <v-radio
                            :label="$t('payElectronic')"
                            value="electronic"
                            :disabled="selectedPaymentItem.paymentDisabled || disabledComponent"
                          />
                          <v-radio
                            :label="$t('payedByHand')"
                            value="byHand"
                            :disabled="selectedPaymentItem.paymentDisabled || disabledComponent"
                          />
                        </v-radio-group>
                      </v-row>
                      <v-row
                        v-else
                        justify="center"
                      >
                        <v-switch
                          v-model="validPayment"
                          :disabled="selectedPaymentItem.paymentDisabled || disabledComponent"
                          :label="$t('payedByHand')"
                        />
                      </v-row>
                    </v-col>
                  </v-row>

                  <!-- if validation -->
                  <v-row v-else>
                    <v-col>
                      <v-row
                        justify="center"
                        class="mt-4"
                      >
                        <v-col>
                          <p>
                            {{ $t('price', {price: priceTravel}) }}
                          </p>
                        </v-col>
                      </v-row>
                      <v-row
                        v-if="!selectedPaymentItem.reported"
                        justify="center"
                        class="mt-n10"
                      >
                        <v-col>
                          <v-dialog
                            v-model="dialog"
                            persistent
                            max-width="330"
                          >
                            <template v-slot:activator="{ on }">
                              <v-btn
                                text
                                class="error--text text-decoration-underline text-lowercase"
                                :disabled="selectedPaymentItem.paymentDisabled || disabledComponent"
                                v-on="on"
                              >
                                {{ $t('report.label') }}
                              </v-btn>
                            </template>
                            <v-card>
                              <v-card-title class="text-h5 primary--text">
                                {{ $t('report.label') }}
                              </v-card-title>
                              <v-card-text>
                                <p> {{ $t('report.text1') }}</p><p class="font-italic">
                                  {{ $t('report.text2') }}
                                </p>
                              </v-card-text>
                              <v-card-actions>
                                <v-spacer />
                                <v-btn
                                  color="secondary"
                                  text
                                  :loading="loading"
                                  @click="dialog = false"
                                >
                                  {{ $t('report.cancel') }}
                                </v-btn>
                                <v-btn
                                  color="secondary"
                                  text
                                  :loading="loading"
                                  @click="sendReport"
                                >
                                  {{ $t('report.valid') }}
                                </v-btn>
                              </v-card-actions>
                            </v-card>
                          </v-dialog>
                        </v-col>
                      </v-row>
                      <v-row
                        v-else
                        justify="center"
                        class="mt-n10"
                      >
                        <v-col>
                          <v-btn
                            text
                            class="error--text text-lowercase"
                            :disabled="selectedPaymentItem.paymentDisabled || disabledComponent"
                          >
                            <v-icon class="mr-2 ml-n2">
                              mdi-alert-outline
                            </v-icon>  
                            {{ $t('report.labelIsReported') }}
                          </v-btn>
                        </v-col>
                      </v-row>
                      <v-row
                        justify="center"
                        class="mt-4"
                      >
                        <v-col>
                          <v-btn
                            v-if="selectedPaymentItem.confirmed"
                            color="secondary"
                            outlined
                            rounded
                            :disabled="selectedPaymentItem.paymentDisabled || disabledComponent"
                            @click="confirmPayment"
                          >
                            <v-icon class="mr-2 ml-n2">
                              mdi-check
                            </v-icon>                          
                            {{ $t('buttons.isConfirmed') }}
                          </v-btn>
                          <v-btn
                            v-else
                            color="secondary"
                            rounded
                            :disabled="selectedPaymentItem.paymentDisabled || disabledComponent"
                            @click="confirmPayment"
                          >
                            {{ $t('buttons.confirmByHandPayment') }}
                          </v-btn>
                        </v-col>
                      </v-row>
                    </v-col>
                  </v-row>
                </v-card-text>
              </v-row>

              <!-- actions buttons -->
              <v-card-actions>
                <v-row
                  justify="center"
                >
                  <v-col>
                    <v-btn
                      v-if="previousPaymentItem"
                      rounded
                      outlined
                      :disabled="disabledComponent"
                      color="secondary"
                      @click="previousPayment"
                    >
                      <v-icon class="ml-n2">
                        mdi-menu-left
                      </v-icon>
                      {{ $t('buttons.previous') }}
                    </v-btn>
                  </v-col>
                  <v-col>
                    <v-btn
                      v-if="nextPaymentItem"
                      rounded
                      outlined
                      color="secondary"
                      :disabled="disabledComponent"
                      @click="nextPayment"
                    >
                      {{ $t('buttons.next') }}
                      <v-icon class="mr-n2">
                        mdi-menu-right
                      </v-icon>
                    </v-btn>
                  </v-col>
                </v-row>
              </v-card-actions>
            </v-card>
          </v-col>

          <!-- next journey -->
          <v-col
            cols="2"
            align="center"
          >
            <v-card
              v-if="nextPaymentItem"
              raised
              height="950"
              class="mx-auto"
            >
              <v-row
                justify="center"
                class="pt-5"
              >
                <v-avatar size="75">
                  <img
                    v-if="nextPaymentItem.avatar"
                    :src="nextPaymentItem.avatar"
                  >
                  <img
                    v-else
                    src="/images/avatarsDefault/square_250.svg"
                  >
                </v-avatar>
              </v-row>
              <v-row justify="center">
                <v-card-title>
                  <p class="text-body-2">
                    {{ nextPaymentItem.givenName }} {{ nextPaymentItem.shortFamilyName }}.
                  </p>
                </v-card-title>
              </v-row>
            </v-card>
          </v-col>
        </v-row>
      </v-col>

      <!-- payments sum and informations -->
      <v-col
        v-if="isPayment"
        cols="4"
      >
        <v-row
          justify="center"
          align="center"
        >
          <v-card
            outlined
            color="primary lighten-3"
            height="100"
            width="500"
          >
            <v-col
              align="center"
              class="white--text mt-6 font-weight-bold "
            >
              {{ $t('wallet') }}
            </v-col>
          </v-card>
        </v-row>
        <v-row
          v-if="pricesByHand.length > 0"
          justify="center"
        >
          <v-col
            align="center"
            class="font-weight-bold grey--text"
          >
            {{ $t('payedByHand') }} :
          </v-col>
          <v-col
            align="left"
          >
            <v-list shaped>
              <v-list-item-group
                color="primary"
              >
                <v-list-item
                  v-for="(item, i) in pricesByHand"
                  :key="i"
                >
                  <v-list-item-content class="grey--text">
                    <v-row justify="center">
                      <v-col
                        align="center"
                       
                        cols="5"
                      >
                        <p class="my-n2">
                          {{ item.name }} 
                        </p>
                      </v-col>
                      <v-col cols="4">
                        <p class="font-weight-bold my-n2">
                          {{ item.price }} €
                        </p>
                      </v-col>
                      <v-col class="my-n4">
                        <v-btn
                          color="secondary"
                          fab
                          x-small
                          :disabled="disabledComponent"
                          @click="removeByHandPayment(i, item)"
                        >
                          <v-icon>
                            mdi-trash-can
                          </v-icon>
                        </v-btn>
                      </v-col>
                    </v-row>
                  </v-list-item-content>
                </v-list-item>
              </v-list-item-group>
            </v-list>
          </v-col>
        </v-row>
        <v-divider v-if="displayElectronicPayment && pricesElectronic.length > 0" />
        <v-row
          v-if="displayElectronicPayment && pricesElectronic.length > 0"
          justify="center"
        >
          <v-col
            align="center"
            class="font-weight-bold"
          >
            {{ $t('payElectronic') }} :
          </v-col>
          <v-col align="left">
            <v-list
              shaped
            >
              <v-list-item-group
                color="primary"
              >
                <v-list-item
                  v-for="(item, i) in pricesElectronic"
                  :key="i"
                >
                  <v-list-item-content>
                    <v-row justify="center">
                      <v-col
                        align="center"
                        cols="5"
                      >
                        <p class="my-n2">
                          {{ item.name }} 
                        </p>
                      </v-col>
                      <v-col cols="4">
                        <p class="font-weight-bold my-n2">
                          {{ item.price }} €
                        </p>
                      </v-col>
                      <v-col class="my-n4">
                        <v-btn
                          color="secondary"
                          fab
                          x-small
                          :disabled="disabledComponent"
                          @click="removeElectronicPayment(i, item)"
                        >
                          <v-icon>
                            mdi-trash-can
                          </v-icon>
                        </v-btn>
                      </v-col>
                    </v-row>
                  </v-list-item-content>
                </v-list-item>
              </v-list-item-group>
            </v-list>
          </v-col>
        </v-row>
        <!-- donations -->
        <!-- <v-row
          justify="center"
          class="mb-4"
        >
          <v-card
            outlined
            color="primary lighten-3"
            height="100"
            width="500"
          >
            <v-col
              align="center"
              class="white--text mt-6 font-weight-bold"
            >
              {{ $t('donations') }}
            </v-col>
          </v-card>
        </v-row> -->
        <v-row
          v-if="displayElectronicPayment && pricesElectronic.length > 0"
          justify="center"
        >
          <v-col align="center">
            <p class="text-h3">
              {{ $t('sumToPay', {price: sumTopay}) }}
            </p>
          </v-col>
        </v-row>
        <v-row
          v-if="pricesElectronic.length > 0 || pricesByHand.length > 0"
          justify="center"
        >
          <v-btn
            rounded
            color="success"
            :loading="loading"
            @click="sendValidatedPayments"
          >
            {{ $t('buttons.validate') }}
          </v-btn>
        </v-row>
      </v-col>

      <!-- validate that by hand payment was done -->
      <v-col v-else>
        <v-row
          v-if="paymentsByHandConfirmed.length > 0"
          justify="center"
        >
          <v-col
            align="center"
            class="font-weight-bold"
          >
            {{ $t('paymentReceivedByHand') }} :
          </v-col>
          <v-col
            align="left"
          >
            <v-list shaped>
              <v-list-item-group
                v-model="paymentsByHandConfirmed"
                color="primary"
              >
                <v-list-item
                  v-for="(item, i) in paymentsByHandConfirmed"
                  :key="i"
                >
                  <v-list-item-content class="grey--text">
                    <v-row justify="center">
                      <v-col
                        align="center"
                        cols="5"
                      >
                        <p class="my-n2">
                          {{ item.name }} 
                        </p>
                      </v-col>
                      <v-col cols="4">
                        <p class="font-weight-bold my-n2">
                          {{ item.price }} €
                        </p>
                      </v-col>
                      <v-col class="my-n4">
                        <v-btn
                          color="secondary"
                          fab
                          x-small
                          @click="removeConfirmedPayment(i, item)"
                        >
                          <v-icon>
                            mdi-trash-can
                          </v-icon>
                        </v-btn>
                      </v-col>
                    </v-row>
                  </v-list-item-content>
                </v-list-item>
              </v-list-item-group>
            </v-list>
          </v-col>
        </v-row>
        <v-row
          v-if="paymentsByHandConfirmed.length > 0"
          justify="center"
        >
          <v-col align="center">
            <v-btn
              rounded
              color="secondary"
              :loading="loading"
              :disbled="disabledComponent"
              @click="sendValidatedPayments"
            >
              {{ $t('buttons.confirm') }}
            </v-btn>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </v-container>
</template>
<script>
import axios from 'axios';
import moment from "moment";
import DayListChips from "@components/utilities/DayListChips";
import Translations from "@translations/components/payment/payment.json";


export default {
  i18n: {
    messages: Translations,
  },
  components: {
    DayListChips
  },
  props: {
    paymentElectronicActive: {
      type: Boolean,
      default: null
    },
    frequency: {
      type: Number,
      default: 2
    },
    type: {
      type: Number,
      default: 2
    },
    selectedId: {
      type: Number,
      default: 4
    },
  },
  data() {
    return {
      locale: this.$i18n.locale,
      message:null,
      // props
      displayElectronicPayment: this.paymentElectronicActive,
      regular: this.frequency == 1 ? false : true,
      isPayment: this.type == 1 ? true : false,
      selectedItemId: this.selectedId,
      // all paymentItems
      paymentItems: null,
      // selected, next and previous paymentItems 
      selectedPaymentItem: null,
      previousPaymentItem: null,
      nextPaymentItem: null,
      selectedKey: null,
      previousKey: null,
      nextKey: null,
      date: null,
      daysList: null,

      sumTopay:0,
      validPayment: false,
      modeOfPayment: null,
      priceTravel: null,

      weekSelected: 292020,
      paymentPayment: {
        "type": this.type,  
        "items": null
      },

      loading: false,
      disabledComponent: false,
      dialog: false,
      periods: ['du 08/05/20 au 15/05/20', 'du 16/05/20 au 23/05/20'],
      pricesElectronic: [],
      pricesByHand: [],
      paymentsByHandConfirmed: []
    };
  },
    
  watch: {
    // method to validate a by hand payement we electronic payment is disabled
    validPayment () {
      if (this.validPayment == true && this.selectedPaymentItem.paymentDisabled == false) {
        this.validatePayment('byHand');
      }
    },
    // method to indicate the mode of payment ('by hand' or 'electronic') and to validate the payment
    modeOfPayment() {
      if (this.modeOfPayment == 'byHand'&& this.selectedPaymentItem.paymentDisabled == false) {
        this.validatePayment('byHand');
      } else if (this.modeOfPayment == 'electronic' && this.selectedPaymentItem.paymentDisabled == false) {
        this.validatePayment('electronic');
      }
    }
  },
  mounted () {
    // we set params
    let params = {
      'frequency':this.frequency,
      'type':this.type,
      'week':this.weekSelected
    }
    // we get all paymentItems
    axios.post(this.$t("payments.getPayments"), params)
      .then(res => {
        this.paymentItems = res.data;

        // we select the displayed paymentItems (selected, next and previous)
        this.paymentItems.forEach((paymentItem, key) => {
          paymentItem.paymentIsvalidated = false;
          paymentItem.paymentDisabled = false;
          paymentItem.modeOfPayment = false;
          paymentItem.reported = false;
          paymentItem.confirmed = true;
          if (paymentItem.id == this.selectedItemId) {
            // we set key and payment of the selected payment
            this.selectedPaymentItem = paymentItem;
            this.selectedKey = key;

            // we set key and payment of the next payment
            this.nextKey = (key + 1) <= (this.paymentItems.length - 1) ? (key + 1) : null;
            this.nextPaymentItem = this.paymentItems[this.nextKey] ? this.paymentItems[this.nextKey] : null;

            // we set key and payment of the previous payment
            this.previousKey = (key - 1) > 0 ? (key - 1) : null;
            this.previousPaymentItem = this.paymentItems[this.previousKey] ? this.paymentItems[this.previousKey] : null;
            
            // we format the date for punctual
            this.formatDate(this.selectedPaymentItem);
            // we calculate the amout to display
            this.amoutTodisplay(this.selectedPaymentItem);
            
          }
        });
      });
  },
  created() {
    moment.locale(this.locale); 
  },
  methods: {
    // method to update the dayslist of regular payment
    updateDaysList(daysList) {
      if (daysList.isOutward) {
        this.selectedPaymentItem.outwardDays[0]['status'] = daysList.mon 
        this.selectedPaymentItem.outwardDays[1]['status'] = daysList.tue
        this.selectedPaymentItem.outwardDays[2]['status'] = daysList.wed
        this.selectedPaymentItem.outwardDays[3]['status'] = daysList.thu
        this.selectedPaymentItem.outwardDays[4]['status'] = daysList.fri
        this.selectedPaymentItem.outwardDays[5]['status'] = daysList.sat
        this.selectedPaymentItem.outwardDays[6]['status'] = daysList.sun
      } else if (!daysList.isOutward) {
        this.selectedPaymentItem.returnDays[0]['status'] = daysList.mon 
        this.selectedPaymentItem.returnDays[1]['status'] = daysList.tue
        this.selectedPaymentItem.returnDays[2]['status'] = daysList.wed
        this.selectedPaymentItem.returnDays[3]['status'] = daysList.thu
        this.selectedPaymentItem.returnDays[4]['status'] = daysList.fri
        this.selectedPaymentItem.returnDays[5]['status'] = daysList.sat
        this.selectedPaymentItem.returnDays[6]['status'] = daysList.sun
      }
      this.amoutTodisplay(this.selectedPaymentItem);
    },
    // method to format punctual date
    formatDate(paymentItem) {
      if (paymentItem.date) {
        this.date = moment(paymentItem.date.date).format(this.$t("ll"));
      }
    },
    // method to calculate amount to display
    amoutTodisplay(paymentItem) {
      this.priceTravel = 0;
      if (paymentItem.frequency == 1) {
        this.priceTravel = paymentItem.amount;
      } else if (paymentItem.frequency == 2) {
        let numberOutwardDays = 0;
        let numberReturnDays = 0;
        paymentItem.outwardDays.forEach((day) => {
          if (day.status == 1) {
            numberOutwardDays = numberOutwardDays + 1;
          }
        });
        if (paymentItem.returnDays) {
          paymentItem.returnDays.forEach((day) => {
            if (day.status == 1) {
              numberReturnDays = numberReturnDays + 1;
            }
          });
        }
        this.priceTravel = numberOutwardDays * paymentItem.outwardAmount +  numberReturnDays * paymentItem.returnAmount;
      }
    },
    // method when we click on next
    nextPayment() {
      // we set new selected payment
      this.selectedPaymentItem = this.paymentItems[this.selectedKey + 1];
      this.selectedKey = this.selectedKey + 1;
      // we set new previous payment
      // previousKey == null only if the first selected payment is the first
      if (this.previousKey == null) {
        this.previousPaymentItem = this.paymentItems[0];
        this.previousKey = 0;
      } else {
        this.previousPaymentItem = this.paymentItems[this.previousKey +1];
        this.previousKey = this.previousKey + 1;
      }
     
      if ((this.nextKey + 1) < (this.paymentItems.length - 1)) {
        this.nextPaymentItem = this.paymentItems[this.nextKey + 1];
      } else {
        this.nextPaymentItem = null;
      }
      this.nextKey = this.nextKey + 1;
      // we set date of new selected payment 
      this.formatDate(this.selectedPaymentItem);
      this.amoutTodisplay(this.selectedPaymentItem);
      this.validPayment = this.selectedPaymentItem.paymentIsvalidated;
      this.modeOfPayment = this.selectedPaymentItem.modeOfPayment;
    },
    // method when we click on previous
    previousPayment() {
      // we set new selected payment
      this.selectedPaymentItem = this.paymentItems[this.selectedKey - 1];
      this.selectedKey = this.selectedKey - 1;
      // we set new previous payment
      if ((this.previousKey - 1) >= 0) {
        this.previousPaymentItem = this.paymentItems[this.previousKey -1];
      } else {
        this.previousPaymentItem = null;
      }
      this.previousKey = this.previousKey - 1;
      // we set new next payment
      // nextKey == null only if the first selected payment is the first
      if (this.nextKey == null) {
        this.nextPaymentItem = this.paymentItems[this.paymentItems.length - 1];
        this.nextKey = this.paymentItems.length - 1;
      } else {
        this.nextPaymentItem = this.paymentItems[this.nextKey - 1];
        this.nextKey = this.nextKey - 1;
      }
      // we set date of new selected payment 
      this.formatDate(this.selectedPaymentItem);
      this.amoutTodisplay(this.selectedPaymentItem);
      this.validPayment = this.selectedPaymentItem.paymentIsvalidated;
      this.modeOfPayment = this.selectedPaymentItem.modeOfPayment;

    },
    // method who update payment and total to pay 
    validatePayment(type) {
      this.selectedPaymentItem.paymentIsvalidated = true;
      this.selectedPaymentItem.paymentDisabled = true;
      if (type == 'byHand') {
        this.pricesByHand.push({ id: this.selectedPaymentItem.id, name: this.selectedPaymentItem.givenName + ' ' + this.selectedPaymentItem.shortFamilyName, price: this.priceTravel });
        this.selectedPaymentItem.modeOfPayment = 2;
      } else if (type == 'electronic') {
        this.pricesElectronic.push({ id: this.selectedPaymentItem.id, name: this.selectedPaymentItem.givenName + ' ' + this.selectedPaymentItem.shortFamilyName, price: this.priceTravel });
        this.selectedPaymentItem.modeOfPayment = 1;
        this.sumTopay = this.sumTopay + this.priceTravel;
      } else if (type == 'confirmed') {
        this.paymentsByHandConfirmed.push({id: this.selectedPaymentItem.id, name: this.selectedPaymentItem.givenName + ' ' + this.selectedPaymentItem.shortFamilyName, price: this.priceTravel });
        this.selectedPaymentItem.modeOfPayment = 2;
      } 
    },
    // method to remove by hand payment 
    removeByHandPayment(i, item) {
      // we reset payement parameters of the selected item
      this.paymentItems.forEach((paymentItem, key) => {
        if (paymentItem.id == item.id) {
          paymentItem.paymentIsvalidated = false;
          paymentItem.paymentDisabled = false;
          paymentItem.modeOfPayment = null;
          this.validPayment = paymentItem.paymentIsvalidated;
        }
      });
      // we reset payment parameters of selectedPayment
      if (this.selectedPaymentItem.id == item.id) {
        this.selectedPaymentItem.paymentIsvalidated = false;
        this.selectedPaymentItem.paymentDisabled = false;
        this.selectedPaymentItem.modeOfPayment = null;
        this.validPayment = this.selectedPaymentItem.paymentIsvalidated;
        this.modeOfPayment = this.selectedPaymentItem.modeOfPayment;
      } 
      // we remove the item to the list of validated payments
      this.pricesByHand.splice(i, 1);
    },
    // method to remove electronic payment
    removeElectronicPayment(i, item) {
      // we reset payement parameters of the selected item
      this.paymentItems.forEach((paymentItem, key) => {
        if (paymentItem.id == item.id) {
          paymentItem.paymentIsvalidated = false;
          paymentItem.paymentDisabled = false;
          paymentItem.modeOfPayment = null;
          this.validPayment = paymentItem.paymentIsvalidated;
          this.sumTopay = this.sumTopay - paymentItem.amount < 0 ? 0 : this.sumTopay - paymentItem.amount; 
        }
      });
      // we reset payment parameters of selectedPayment
      if (this.selectedPaymentItem.id == item.id) {
        this.selectedPaymentItem.paymentIsvalidated = false;
        this.selectedPaymentItem.paymentDisabled = false;
        this.selectedPaymentItem.modeOfPayment = null;
        this.validPayment = this.selectedPaymentItem.paymentIsvalidated;
        this.modeOfPayment = this.selectedPaymentItem.modeOfPayment;
        this.sumTopay = this.sumTopay - this.priceTravel < 0 ? 0 : this.sumTopay - this.priceTravel;   
      } 
      this.pricesElectronic.splice(i, 1);
    },
    // method to remove confirmed payment
    removeConfirmedPayment(i, item) {
      // we reset payement parameters of the selected item
      this.paymentItems.forEach((paymentItem, key) => {
        if (paymentItem.id == item.id) {
          paymentItem.paymentIsvalidated = false;
          paymentItem.paymentDisabled = false;
          paymentItem.modeOfPayment = null;
          this.validPayment = paymentItem.paymentIsvalidated;
        }
      });
      // we reset payment parameters of selectedPayment
      if (this.selectedPaymentItem.id == item.id) {
        this.selectedPaymentItem.paymentIsvalidated = false;
        this.selectedPaymentItem.paymentDisabled = false;
        this.selectedPaymentItem.modeOfPayment = null;
        this.validPayment = this.selectedPaymentItem.paymentIsvalidated;
        this.modeOfPayment = this.selectedPaymentItem.modeOfPayment;
      } 
    },
    confirmPayment() {
      if (this.selectedPaymentItem.paymentDisabled == false) {
        this.validatePayment('confirmed');
      }
    },
    // method to send confirmed or reported payments to ddb
    sendValidatedPayments() {
      this.loading = true;
      this.disabledComponent = true;
      let payments = [];
      this.paymentItems.forEach((paymentItem) => {
        // if punctual 
        if (this.frequency == 1) {
          payments.push({"id":paymentItem.id, "mode":paymentItem.modeOfPayment, "status":1});
        } else {
          // if regular 
          // we add all available days of the outward travel
          paymentItem.outwardDays.forEach((day) => {
            if (day.id) {
              payments.push({"id":day.id, "mode":paymentItem.modeOfPayment, "status":day.status});
            }
          })
          // we add all available days of the return travel if return travel exist
          if (paymentItem.returnDays.length > 0) {
            paymentItem.returnDays.forEach((day) => {
              if (day.id) {
                payments.push({"id":day.id, "mode":paymentItem.modeOfPayment, "status":day.status});
              }
            })
          }
        }
      });
      this.paymentPayment.items = payments;
      // we post datas
      axios.post(this.$t("payments.postPayments"), this.paymentPayment)
        .then(res => {
          window.location.href = this.$t("redirectAfterPayment");
        })
        .catch((error) => {
          this.disabledComponent = false;
          this.loading = false;
          console.error(error);
        });
    },
    // method to send confirmed or reported payments to ddb
    sendReport() {
      this.loading = true;
      this.disabledComponent = true;
      this.selectedPaymentItem.paymentIsvalidated = false;
      this.selectedPaymentItem.paymentDisabled = true;
      this.selectedPaymentItem.modeOfPayment = 2;
      let payments = [];
      // if punctual 
      if (this.frequency == 1) {
        payments.push({"id":this.selectedPaymentItem.id, "mode":2, "status":3});
      } else {
        // if regular 
        // we add all available days of the outward travel
        this.selectedPaymentItem.outwardDays.forEach((day) => {
          if (day.id) {
            payments.push({"id":day.id, "mode":2, "status":3});
          }
        })
        // we add all available days of the return travel if return travel exist
        if (this.selectedPaymentItem.returnDays.length > 0) {
          this.selectedPaymentItem.returnDays.forEach((day) => {
            if (day.id) {
              payments.push({"id":day.id, "mode":2, "status":3});
            }
          })
        }
      }
      this.paymentPayment.items = payments;
     

      // we post datas
      axios.post(this.$t("payments.postPayments"), this.paymentPayment)
        .then(res => {
          this.paymentItems.splice(this.selectedKey, 1);
          if (this.this.paymentItems.length > 0) {
            this.nextPayment();
          } else {
            window.location.href = this.$t("redirectAfterPayment");
          }
          this.loading = false;
        })
        .catch((error) => {
          console.error(error);
        });
    },
    
  }
};
</script>