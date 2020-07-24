<template>
  <v-container>
    <v-row justify="center">
      <v-col align="center">
        <h1>
          {{ $t('title') }}
        </h1>
      </v-col>
    </v-row>

    <v-row>
      <v-col>
        <v-btn
          rounded
          color="secondary"
        >
          <v-icon class="ml-n2">
            mdi-menu-left
          </v-icon>
          {{ $t('buttons.back') }}
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
            v-if="previousPaymentItem"
            cols="2"
          >
            <v-card
              raised
              height="950"
              class="mx-auto"
              disabled
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
                      <v-col justify="center">
                        <day-list-chips 
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
                          @change="test"
                        />
                      </v-col>
                    </v-row>
                    <v-row justify="center">
                      <v-col
                        cols="3"
                        class="accent--text mt-3"
                      >
                        {{ $t('return') }}
                        <v-icon color="accent">
                          mdi-arrow-left-bold
                        </v-icon>
                      </v-col>
                      <v-col justify="center">
                        <day-list-chips
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
                        />
                      </v-col>
                    </v-row>
                    <v-row
                      justify="center"
                      class="mt-6"
                    >
                      <p>{{ $t('regularInfo', {driver: selectedPaymentItem.givenName +' '+ selectedPaymentItem.shortFamilyName}) }}</p>
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
                          v-model="validTypeOfPayment"
                          column
                        >
                          <v-radio
                            :label="$t('payElectronic')"
                            value="electronic"
                            :disabled="selectedPaymentItem.paymentDisabled"
                          />
                          <v-radio
                            :label="$t('payedByHand')"
                            value="byHand"
                            :disabled="selectedPaymentItem.paymentDisabled"
                          />
                        </v-radio-group>
                      </v-row>
                      <v-row
                        v-else
                        justify="center"
                      >
                        <v-switch
                          v-model="validPayment"
                          :disabled="selectedPaymentItem.paymentDisabled"
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
                        justify="center"
                        class="mt-n10"
                      >
                        <v-col>
                          <a class="error--text text-decoration-underline">
                            {{ $t('report') }}
                          </a>
                        </v-col>
                      </v-row>
                      <v-row
                        justify="center"
                        class="mt-4"
                      >
                        <v-col>
                          <v-btn
                            color="secondary"
                            rounded
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
            v-if="nextPaymentItem"
            cols="2"
            align="center"
          >
            <v-card
              raised
              height="950"
              class="mx-auto"
              disabled
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
                       
                        cols="6"
                      >
                        <p class="my-n2">
                          {{ item.name }} 
                        </p>
                      </v-col>
                      <v-col>
                        <p class="font-weight-bold my-n2">
                          {{ item.price }} €
                        </p>
                      </v-col>
                      <v-col class="my-n4">
                        <v-btn
                          color="secondary"
                          fab
                          x-small
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
                        cols="6"
                      >
                        <p class="my-n2">
                          {{ item.name }} 
                        </p>
                      </v-col>
                      <v-col>
                        <p class="font-weight-bold my-n2">
                          {{ item.price }} €
                        </p>
                      </v-col>
                      <v-col class="my-n4">
                        <v-btn
                          color="secondary"
                          fab
                          x-small
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
          >
            {{ $t('buttons.validate') }}
          </v-btn>
        </v-row>
      </v-col>

      <!-- validate that by hand payment was done -->
      <v-col v-else>
        <v-row justify="center">
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
                v-model="payedByHand"
                color="primary"
              >
                <v-list-item
                  v-for="(item, i) in payedByHand"
                  :key="i"
                >
                  <v-list-item-content class="grey--text">
                    <v-row justify="center">
                      <v-col align="center">
                        <p>
                          {{ Montanttem.name }} 
                        </p>
                        <p class="font-weight-bold">
                          {{ item.price }} €
                        </p>
                      </v-col>
                      <v-col class="mt-3">
                        <v-btn
                          color="secondary"
                          fab
                          x-small
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
          justify="center"
        >
          <v-col align="center">
            <v-btn
              rounded
              color="secondary"
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
    mode: {
      type: Number,
      default: 1
    },
    selectedId: {
      type: Number,
      default: 1
    },
  },
  data() {
    return {
      locale: this.$i18n.locale,
      message:null,
      // props
      displayElectronicPayment: this.paymentElectronicActive,
      regular: this.frequency == 1 ? false : true,
      isPayment: this.mode == 1 ? true : false,
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
      validTypeOfPayment: null,
      priceTravel: null,

      weekSelected: null,
      
      periods: ['du 08/05/20 au 15/05/20', 'du 16/05/20 au 23/05/20'],
      pricesElectronic: [],
      pricesByHand: [],
      payedByHand: [
        { name: 'Lara C.', price: '30' },
        { name: 'Bruce W.', price: '25' }
      ]
    };
  },
    
  watch: {
    validPayment () {
      if (this.validPayment == true && this.selectedPaymentItem.paymentDisabled == false) {
        this.validatePayment('byHand');
      }
    },
    validTypeOfPayment() {
      if (this.validTypeOfPayment == 'byHand'&& this.selectedPaymentItem.paymentDisabled == false) {
        this.validatePayment('byHand');
      } else if (this.validTypeOfPayment == 'electronic' && this.selectedPaymentItem.paymentDisabled == false) {
        this.validatePayment('electronic');
      }
    }
  },
  mounted () {
    // we set params
    let params = {
      'frequency':this.frequency,
      'type':this.mode,
      'week':this.weekSelected
    }
    // we get all paymentItems
    axios.post(this.$t("payments.route"), params)
      .then(res => {
        this.paymentItems = res.data;

        // we select the displayed paymentItems (selected, next and previous)
        this.paymentItems.forEach((paymentItem, key) => {
          paymentItem.paymentIsvalidated = false;
          paymentItem.paymentDisabled = false;
          paymentItem.validTypeOfPayment = false;
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
    test(daysList) {
     
      console.error(daysList);
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
        paymentItem.returnDays.forEach((day) => {
          if (day.status == 1) {
            numberReturnDays = numberReturnDays + 1;
          }
        });
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
      this.validTypeOfPayment = this.selectedPaymentItem.validTypeOfPayment;
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
      this.validTypeOfPayment = this.selectedPaymentItem.validTypeOfPayment;

    },

    validatePayment(type) {
      this.selectedPaymentItem.paymentIsvalidated = true;
      this.selectedPaymentItem.paymentDisabled = true;
      if (type == 'byHand') {
        this.pricesByHand.push({ id: this.selectedPaymentItem.id, name: this.selectedPaymentItem.givenName + ' ' + this.selectedPaymentItem.shortFamilyName, price: this.priceTravel });
        this.selectedPaymentItem.validTypeOfPayment = 'byHand';
      } else if (type == 'electronic') {
        this.pricesElectronic.push({ id: this.selectedPaymentItem.id, name: this.selectedPaymentItem.givenName + ' ' + this.selectedPaymentItem.shortFamilyName, price: this.priceTravel });
        this.selectedPaymentItem.validTypeOfPayment = 'electronic';
        this.sumTopay = this.sumTopay + this.priceTravel;
      }
    },
    removeByHandPayment(i, item) {
      // we reset payement parameters of the selected item
      this.paymentItems.forEach((paymentItem, key) => {
        if (paymentItem.id == item.id) {
          paymentItem.paymentIsvalidated = false;
          paymentItem.paymentDisabled = false;
          paymentItem.validTypeOfPayment = null;
          this.validPayment = paymentItem.paymentIsvalidated;
        }
      });
      // we reset payment parameters of selectedPayment
      if (this.selectedPaymentItem.id == item.id) {
        this.selectedPaymentItem.paymentIsvalidated = false;
        this.selectedPaymentItem.paymentDisabled = false;
        this.selectedPaymentItem.validTypeOfPayment = null;
        this.validPayment = this.selectedPaymentItem.paymentIsvalidated;
        this.validTypeOfPayment = this.selectedPaymentItem.validTypeOfPayment;
      } 
      // we remove the item to the list of validated payments
      this.pricesByHand.splice(i, 1);
    },
    removeElectronicPayment(i, item) {
      // we reset payement parameters of the selected item
      this.paymentItems.forEach((paymentItem, key) => {
        if (paymentItem.id == item.id) {
          paymentItem.paymentIsvalidated = false;
          paymentItem.paymentDisabled = false;
          paymentItem.validTypeOfPayment = null;
          this.validPayment = paymentItem.paymentIsvalidated;
          this.sumTopay = this.sumTopay - paymentItem.amount < 0 ? 0 : this.sumTopay - paymentItem.amount; 
        }
      });
      // we reset payment parameters of selectedPayment
      if (this.selectedPaymentItem.id == item.id) {
        this.selectedPaymentItem.paymentIsvalidated = false;
        this.selectedPaymentItem.paymentDisabled = false;
        this.selectedPaymentItem.validTypeOfPayment = null;
        this.validPayment = this.selectedPaymentItem.paymentIsvalidated;
        this.validTypeOfPayment = this.selectedPaymentItem.validTypeOfPayment;
        this.sumTopay = this.sumTopay - this.priceTravel < 0 ? 0 : this.sumTopay - this.priceTravel;   
      } 
      this.pricesElectronic.splice(i, 1);
    }
  }
 
 
};
</script>