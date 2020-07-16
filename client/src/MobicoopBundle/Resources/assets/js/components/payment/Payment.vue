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
        class="d-flex"
        cols="12"
        sm="6"
      >
        <v-select
          :items="items"
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
          <v-col cols="3">
            <v-card
              raised
              height="770"
              class="mx-auto"
              disabled
            >
              <v-row
                justify="center"
                class="pt-5"
              >
                <v-avatar size="125">
                  <img
                    src="http://localhost:8081/images/avatarsDefault/square_250.svg"
                  >
                </v-avatar>
              </v-row>
              <v-row justify="center">
                <v-card-title>
                  <p>
                    Tony S.
                  </p>
                </v-card-title>
              </v-row>
            </v-card>
          </v-col>
          <!-- selected journey -->
          <v-col
            cols="6"
            align="center"
          >
            <v-card
              raised
              class="mx-auto"
              height="770"
            >
              <v-row
                justify="center"
                class="pt-5"
              >
                <v-avatar size="125">
                  <img
                    src="http://localhost:8081/images/avatarsDefault/square_250.svg"
                  >
                </v-avatar>
              </v-row>
              <v-row justify="center">
                <v-card-title>
                  <p>
                    Lara C.
                  </p>
                </v-card-title>
              </v-row>
              <v-row justify="center">
                <v-card-text>
                  <!-- dates -->
                  <v-row justify="center">
                    <p class="font-weight-bold">
                      Sam. 25/09
                    </p>
                  </v-row>
                  <!-- journey -->
                  <v-row
                    justify="center"
                  >
                    <v-col>
                      <p class="font-weight-bold">
                        Nancy
                      </p>
                      <p>
                        Rue de la Monnaie
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
                        Metz
                      </p>
                      <p>
                        rue de Nancy
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
                        <day-list-chips />
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
                        <day-list-chips />
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
                        <v-col>
                          <p>
                            {{ $t('price', {price: this.price}) }}
                          </p>
                        </v-col>
                      </v-row>
                      <v-row justify="center">
                        <v-radio-group
                          v-model="radios"
                          column
                        >
                          <v-radio
                            :label="$t('payOnLine')"
                            value="radio-1"
                          />
                          <v-radio
                            :label="$t('payedByHand')"
                            value="radio-2"
                          />
                        </v-radio-group>
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
                            {{ $t('price', {price: this.price}) }}
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
                      rounded
                      outlined
                      color="secondary"
                    >
                      <v-icon class="ml-n2">
                        mdi-menu-left
                      </v-icon>
                      {{ $t('buttons.previous') }}
                    </v-btn>
                  </v-col>
                  <v-col>
                    <v-btn
                      rounded
                      outlined
                      color="secondary"
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
            cols="3"
            align="center"
          >
            <v-card
              raised
              height="770"
              class="mx-auto"
              disabled
            >
              <v-row
                justify="center"
                class="pt-5"
              >
                <v-avatar size="125">
                  <img
                    src="http://localhost:8081/images/avatarsDefault/square_250.svg"
                  >
                </v-avatar>
              </v-row>
              <v-row justify="center">
                <v-card-title>
                  <p>
                    Nathan D.
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
        <v-row justify="center">
          <v-col
            align="center"
            class="font-weight-bold"
          >
            {{ $t('payOnLine') }} :
          </v-col>
          <v-col align="left">
            <v-list shaped>
              <v-list-item-group
                v-model="pricesOnLine"
                color="primary"
              >
                <v-list-item
                  v-for="(item, i) in pricesOnLine"
                  :key="i"
                >
                  <v-list-item-content>
                    <v-row justify="center">
                      <v-col align="center">
                        <p>
                          {{ item.name }} 
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
        <v-row justify="center">
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
                v-model="pricesByHand"
                color="primary"
              >
                <v-list-item
                  v-for="(item, i) in pricesByHand"
                  :key="i"
                >
                  <v-list-item-content class="grey--text">
                    <v-row justify="center">
                      <v-col align="center">
                        <p>
                          {{ item.name }} 
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
        <v-row justify="center">
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
        </v-row>
        <v-row justify="center">
          <v-col align="center">
            <p>
              {{ $t('sumToPay', {price: this.price}) }}
            </p>
          </v-col>
        </v-row>
        <v-row justify="center">
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
                          {{ item.name }} 
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
        <v-row justify="center">
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
import moment from "moment";
import { merge } from "lodash";
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
    frequency: {
      type: Number,
      default: 1
    },
    mode: {
      type: Number,
      default: 2
    },
    selectedId: {
      type: Number,
      default: null
    },
  },
  data() {
    return {
      locale: this.$i18n.locale,
      message:null,
      regular: this.frequency == 1 ? false : true,
      isPayment: this.mode == 1 ? true : false,
      selectedJourney: this.selectedId,
      price: 10,
      items: ['du 08/05/20 au 15/05/20', 'du 16/05/20 au 23/05/20'],
      pricesOnLine: [
        { name: 'Lara C.', price: '30' },
        { name: 'Bruce W.', price: '25' }
      ],
      pricesByHand: [
        { name: 'Tony S.', price: '40' },
        { name: 'Nathan D.', price: '30' },
        { name: 'Bruce W.', price: '25' },
        { name: 'Peter P.', price: '10' }
      ],
      payedByHand: [
        { name: 'Lara C.', price: '30' },
        { name: 'Bruce W.', price: '25' }
      ]
    };
  },
  created() {
    moment.locale(this.locale); 
  }
 
};
</script>