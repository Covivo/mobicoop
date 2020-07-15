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
      <v-col
        cols="8"
        align="center"
      >
        <v-row>
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
                  Lara C.
                </v-card-title>
              </v-row>
              <v-row justify="center">
                <v-card-text>
                  <v-row justify="center">
                    <p>Sam. 25/09</p>
                  </v-row>
                  <v-row
                    justify="center"
                  >
                    <v-col>
                      <p class="font-weight-bold">
                        Nancy
                      </p>
                      <p>Rue de la Monnaie</p>
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
                      <p>rue de Nancy</p>
                    </v-col>
                  </v-row>
                  <!-- if regular journey -->
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
                </v-card-text>
              </v-row>
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
      <v-col cols="4">
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
                    <v-row>
                      <v-col>
                        <p>
                          {{ item }} €
                        </p>
                      </v-col>
                      <v-col class="mt-n2">
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
            <v-list>
              <v-list-item-group
                v-model="pricesByHand"
                color="primary"
              >
                <v-list-item
                  v-for="(item, i) in pricesByHand"
                  :key="i"
                >
                  <v-list-item-content class="grey--text">
                    <v-row>
                      <v-col>
                        <p>
                          {{ item }} €
                        </p>
                      </v-col>
                      <v-col class="mt-n2">
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
      default: 1
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
      price: 55,
      items: ['du 08/05/20 au 15/05/20', 'du 16/05/20 au 23/05/20'],
      pricesOnLine: ['30', '25'],
      pricesByHand: ['30', '25', "45", "10"],
    };
  },
  created() {
    moment.locale(this.locale); 
  }
 
};
</script>