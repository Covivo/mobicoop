<template>
  <v-container>
    <v-row
      align="center"
    >
      <!-- BACK BUTTON -->
      <v-col
        class="mt-0 mb-0 mr-0 ml-3 pa-0"
        cols="1"
        justify="left"
      >
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

      <!-- TITLE -->
      <v-col
        align="center"
        cols="8"
        class="pl-12 ml-12"
      >
        <!-- Driver : confirmation -->
        <h1
          v-if="!isPayment"
          class="pl-12 ml-12"
        >
          {{ $t('titleConfirmation') }}
        </h1>
        <!-- Passenger : regular payment -->
        <h1
          v-else-if="regular"
          class="pl-12 ml-12"
        >
          {{ $t('titleRegular') }}
        </h1>
        <!-- Passenger : punctual payment -->
        <h1
          v-else
          class="pl-12 ml-12"
        >
          {{ $t('titlePunctual') }}
        </h1>
      </v-col>  
    </v-row>

    <!-- WEEK SELECTION (regular only) -->
    <v-row
      v-if="regular"
      justify="center"
      align="center"
    >
      <v-col
        align="center"
        cols="2"
        class=" mr-n6 text-h4 primary--text "
      >
        <p>
          {{ $t('select.label') }}
        </p>
      </v-col>
      <v-col
        class="d-flex"
        cols="4"
      >
        <v-menu
          v-model="menuSelectedWeekDays"
          :close-on-content-click="false"
          transition="scale-transition"
          offset-y
          min-width="290px"
        >
          <template v-slot:activator="{ on }">
            <v-text-field
              :value="computedSelectedWeekDays"
              :label="$t('select.label')"
              readonly
              v-on="on"
            />
          </template>
          <v-date-picker
            v-model="selectedWeekDays"
            :locale="locale"
            no-title
            range
            first-day-of-week="1"
            :locale-first-day-of-year="getFirstDayOfYear()"
            :allowed-dates="allowedDates"
            show-week
            :max="maxDay"
            @click:date="changeWeekDays()"
          />
        </v-menu>
      </v-col>
    </v-row>
    <v-row
      v-if="loadingPage"
      justify="center"
    >
      <v-col cols="8">
        <v-alert
          text
          color="success"
          class="text-center"
        >
          {{ $t('loadingPageText') }}
          <v-progress-linear
            indeterminate
            rounded
          />
        </v-alert>
      </v-col>
    </v-row>      
    <!-- MAIN -->
    <v-row
      v-else-if="currentItem || nextItem || previousItem"
      justify="center"
    >
      <!-- JOURNEY SELECTION ("carousel") -->
      <v-col
        cols="8"
        align="center"
        class="mt-n8"
      >
        <v-row>
          <!-- PREVIOUS JOURNEY -->
          <v-col
            cols="2"
          >
            <v-card
              v-if="previousItem"
              raised
              class="mx-auto"
            >
              <v-row
                justify="center"
                class="pt-2"
              >
                <v-avatar size="75">
                  <img
                    v-if="previousItem.avatar"
                    :src="previousItem.avatar"
                  >
                  <img
                    v-else
                    src="/images/avatarsDefault/square_250.svg"
                  >
                </v-avatar>
              </v-row>
              <v-row
                justify="center"
                class="mb-n12 pb-n12"
              >
                <v-card-title>
                  <p class="text-body-2">
                    {{ previousItem.givenName }} {{ previousItem.shortFamilyName }}
                  </p>
                </v-card-title>
              </v-row>
            </v-card>
          </v-col>

          <!-- CURRENT JOURNEY -->
          <v-col
            cols="8"
            align="center"
          >
            <v-card
              v-if="currentItem"
              raised
              class="mx-auto"
            >
              <!-- avatar -->
              <v-row
                justify="center"
                class="pt-2"
              >
                <v-avatar size="90">
                  <img
                    src="/images/avatarsDefault/square_250.svg"
                  >
                </v-avatar>
              </v-row>

              <!-- carpooler name -->
              <v-row justify="center">
                <v-card-title class="ma-0 pa-0">
                  <p class="mb-0">
                    {{ currentItem.givenName }} {{ currentItem.shortFamilyName }}
                  </p>
                </v-card-title>
              </v-row>

              <!-- departure & arrival -->
              <v-row justify="center">
                <v-card-text class="pa-0 ma-0">
                  <!-- date -->
                  <v-row justify="center">
                    <p class="font-weight-bold">
                      {{ itemDate }}
                    </p>
                  </v-row>
                  <!-- origin & destination -->
                  <v-row
                    justify="center"
                    class="ma-0 pa-0"
                  >
                    <v-col>
                      <p class="font-weight-bold ml-2">
                        {{ currentItem.origin.addressLocality }}
                      </p>
                      <p>
                        {{ currentItem.origin.street }}
                      </p>
                    </v-col>
                    <v-col class="ma-0 pa-0">
                      <v-icon
                        size="60"
                        color="accent"
                      >
                        mdi-ray-start-end
                      </v-icon>
                    </v-col>
                    <v-col>
                      <p class="font-weight-bold mr-2">
                        {{ currentItem.destination.addressLocality }}
                      </p>
                      <p>
                        {{ currentItem.destination.street }}
                      </p>
                    </v-col>
                  </v-row>
                  <!-- regular planning-->
                  <v-row v-if="regular">
                    <v-row
                      v-if="isPayment"
                      justify="center"
                      class="mt-n2"
                    >
                      <v-col
                        cols="10"
                        class="ma-0 pa-0"
                      >
                        <p
                          class="ma-0 pa-0"
                          v-html="$t('regularInfo', {driver: currentItem.givenName +' '+ currentItem.shortFamilyName})"
                        />
                      </v-col>
                    </v-row>
                    <v-row
                      justify="center"
                    >
                      <!-- outward -->
                      <v-col
                        cols="3"
                        class="accent--text pa-0 ma-0"
                      >
                        {{ $t('outward') }}
                        <v-icon color="accent">
                          mdi-arrow-right-bold
                        </v-icon>
                      </v-col>
                      <v-col
                        justify="center"
                        cols="12"
                        class="pa-0 ma-0"
                      >
                        <day-list-chips 
                          :disabled="currentItem.mode !== null || disabledComponent"
                          :is-outward="true"
                          :sun-active="currentItem.outwardDays[0]['status'] == 1 ? true : false"
                          :mon-active="currentItem.outwardDays[1]['status'] == 1 ? true : false"
                          :tue-active="currentItem.outwardDays[2]['status'] == 1 ? true : false"
                          :wed-active="currentItem.outwardDays[3]['status'] == 1 ? true : false"
                          :thu-active="currentItem.outwardDays[4]['status'] == 1 ? true : false"
                          :fri-active="currentItem.outwardDays[5]['status'] == 1 ? true : false"
                          :sat-active="currentItem.outwardDays[6]['status'] == 1 ? true : false"
                          :sun-disabled="currentItem.outwardDays[0]['status'] == 0 ? true : false"
                          :mon-disabled="currentItem.outwardDays[1]['status'] == 0 ? true : false"
                          :tue-disabled="currentItem.outwardDays[2]['status'] == 0 ? true : false"
                          :wed-disabled="currentItem.outwardDays[3]['status'] == 0 ? true : false"
                          :thu-disabled="currentItem.outwardDays[4]['status'] == 0 ? true : false"
                          :fri-disabled="currentItem.outwardDays[5]['status'] == 0 ? true : false"
                          :sat-disabled="currentItem.outwardDays[6]['status'] == 0 ? true : false"
                          @change="updateDaysList"
                        />
                      </v-col>
                    </v-row>

                    <!-- return -->
                    <v-row
                      v-if="currentItem.returnDays"
                      justify="center"
                    >
                      <v-col
                        cols="3"
                        class="accent--text ma-0 pa-0"
                      >
                        {{ $t('return') }}
                        <v-icon color="accent">
                          mdi-arrow-left-bold
                        </v-icon>
                      </v-col>
                      <v-col
                        justify="center"
                        cols="12"
                        class="pa-0 ma-0"
                      >
                        <day-list-chips
                          :is-outward="false"
                          :disabled="currentItem.mode !== null || disabledComponent"
                          :sun-active="currentItem.returnDays[0]['status'] == 1 ? true : false"
                          :mon-active="currentItem.returnDays[1]['status'] == 1 ? true : false"
                          :tue-active="currentItem.returnDays[2]['status'] == 1 ? true : false"
                          :wed-active="currentItem.returnDays[3]['status'] == 1 ? true : false"
                          :thu-active="currentItem.returnDays[4]['status'] == 1 ? true : false"
                          :fri-active="currentItem.returnDays[5]['status'] == 1 ? true : false"
                          :sat-active="currentItem.returnDays[6]['status'] == 1 ? true : false"
                          :sun-disabled="currentItem.returnDays[0]['status'] == 0 ? true : false"
                          :mon-disabled="currentItem.returnDays[1]['status'] == 0 ? true : false"
                          :tue-disabled="currentItem.returnDays[2]['status'] == 0 ? true : false"
                          :wed-disabled="currentItem.returnDays[3]['status'] == 0 ? true : false"
                          :thu-disabled="currentItem.returnDays[4]['status'] == 0 ? true : false"
                          :fri-disabled="currentItem.returnDays[5]['status'] == 0 ? true : false"
                          :sat-disabled="currentItem.returnDays[6]['status'] == 0 ? true : false"
                          @change="updateDaysList"
                        />
                      </v-col>
                    </v-row>
                  </v-row>

                  <!-- Passenger : journey payment section -->
                  <v-row v-if="isPayment">
                    <v-col>
                      <!-- price -->
                      <v-row
                        justify="center"
                        class="mt-n4 mb-0 pa-0"
                      >
                        <v-col>
                          <p>
                            {{ $t('price', {price: getAmount(currentItem)}) }}
                          </p>
                        </v-col>
                      </v-row>
                      <!-- journey reported as unpaid ? -->
                      <v-row
                        v-if="currentItem.unpaidDate"
                        justify="center"
                        class="mt-n8"
                      >
                        <v-col>
                          <v-icon class="mr-2 ml-n2">
                            mdi-alert-outline
                          </v-icon>  
                          {{ $t('report.labelIsReported') }}
                        </v-col>
                      </v-row>
                      <!-- payment mode choice (if ePay enabled) -->
                      <v-row
                        v-if="ePay && currentItem.canPayElectronically"
                        justify="center"
                      >
                        <v-radio-group
                          v-model="currentItem.mode"
                          column
                        >
                          <v-row class="mt-n12"> 
                            <v-col>
                              <v-radio
                                :label="$t('electronicPay')"
                                :value="1"
                                :disabled="!currentItem.electronicallyPayable"
                              />
                            </v-col>
                            
                            <v-tooltip
                              v-if="!currentItem.electronicallyPayable"
                              right
                              color="info"
                            >
                              <template v-slot:activator="{ on }">
                                <v-icon v-on="on">
                                  mdi-help-circle-outline
                                </v-icon>
                              </template>
                              <span>{{ $t('tooltip.message') }}</span>
                            </v-tooltip>
                          </v-row>
                          <v-radio
                            :label="$t('directPay')"
                            :value="2"
                            :disabled="disabledComponent"
                          />
                        </v-radio-group>
                      </v-row>
                      <!-- direct payment confirm button (if ePay disabled) -->
                      <v-row
                        v-else
                        justify="center"
                      >
                        <v-col cols="12">
                          <v-row justify="center">
                            <v-col
                              cols="10"
                              class="text-center"
                            >
                              <v-alert
                                v-if="currentItem.electronicallyPayable && !currentItem.canPayElectronically"
                                type="info"
                              >
                                {{ $t('noOnlinePayment.line1') }}
                                <br>
                                {{ $t('noOnlinePayment.line2') }}
                              </v-alert>
                            </v-col>
                          </v-row>
                          <v-row>
                            <v-col
                              cols="12"
                              class="mt-n12"
                            >
                              <!-- Item already confirmed -->
                              <v-btn
                                v-if="currentItem.mode !== null"
                                color="secondary"
                                disabled
                                outlined
                                rounded
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
                                :disabled="disabledComponent"
                                @click="confirmPayment(2)"
                              >
                                {{ $t('buttons.directPay') }}
                              </v-btn>
                            </v-col>
                          </v-row>
                        </v-col>
                      </v-row>
                    </v-col>
                  </v-row>

                  <!-- Driver : journey confirmation section -->
                  <v-row v-else>
                    <v-col>
                      <v-row
                        justify="center"
                        class="mt-4"
                      >
                        <v-col>
                          <p>
                            {{ $t('price', {price: getAmount(currentItem)}) }}
                          </p>
                        </v-col>
                      </v-row>

                      <!-- Report link -->
                      <v-row
                        v-if="!currentItem.unpaidDate"
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
                                :disabled="currentItem.mode !== null || disabledComponent"
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

                      <!-- Item already reported -->
                      <v-row
                        v-else
                        justify="center"
                        class="mt-n8"
                      >
                        <v-col>
                          <v-icon class="mr-2 ml-n2">
                            mdi-alert-outline
                          </v-icon>  
                          {{ $t('report.labelIsReported') }}
                        </v-col>
                      </v-row>

                      <!-- Item confirmation -->
                      <v-row
                        justify="center"
                        class="mt-4"
                      >
                        <v-col>
                          <!-- Item already confirmed -->
                          <v-btn
                            v-if="currentItem.mode !== null"
                            color="secondary"
                            disabled
                            outlined
                            rounded
                          >
                            <v-icon class="mr-2 ml-n2">
                              mdi-check
                            </v-icon>                          
                            {{ $t('buttons.isConfirmed') }}
                          </v-btn>

                          <!-- Item confirmation -->
                          <v-btn
                            v-else
                            color="secondary"
                            class="mt-n12"
                            rounded
                            :disabled="disabledComponent || getAmount(currentItem)<=0"
                            @click="confirmPayment(2)"
                          >
                            {{ $t('buttons.directPayConfirm') }}
                          </v-btn>
                        </v-col>
                      </v-row>
                    </v-col>
                  </v-row>
                </v-card-text>
              </v-row>

              <!-- Action buttons -->
              <v-card-actions>
                <v-row
                  justify="center"
                  class="mt-sm-n12 mt-md-n10"
                >
                  <!-- previous button -->
                  <v-col class="pa-1">
                    <v-btn
                      v-if="previousItem"
                      rounded
                      outlined
                      :disabled="disabledComponent"
                      color="secondary"
                      @click="currentKey--"
                    >
                      <v-icon class="ml-n2">
                        mdi-menu-left
                      </v-icon>
                      {{ $t('buttons.previous') }}
                    </v-btn>
                  </v-col>
                  <!-- next button -->
                  <v-col class="pa-1">
                    <v-btn
                      v-if="nextItem"
                      rounded
                      outlined
                      color="secondary"
                      :disabled="disabledComponent"
                      @click="currentKey++"
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

          <!-- NEXT JOURNEY -->
          <v-col
            cols="2"
            align="center"
          >
            <v-card
              v-if="nextItem"
              raised
              class="mx-auto"
            >
              <v-row
                justify="center"
                class="pt-2"
              >
                <v-avatar size="75">
                  <img
                    v-if="nextItem.avatar"
                    :src="nextItem.avatar"
                  >
                  <img
                    v-else
                    src="/images/avatarsDefault/square_250.svg"
                  >
                </v-avatar>
              </v-row>
              <v-row justify="center">
                <v-card-title>
                  <p
                    class="text-body-2"
                  >
                    {{ nextItem.givenName }} {{ nextItem.shortFamilyName }}
                  </p>
                </v-card-title>
              </v-row>
            </v-card>
          </v-col>
        </v-row>
      </v-col>

      <!-- PAYMENT / CONFIRMATION SECTION -->
      <v-col
        cols="4"
      >
        <!-- Wallet -->
        <!-- <v-row
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
        </v-row> -->

        <!-- Direct payment / confirmation -->
        <v-row
          v-if="directItems.length > 0"
          justify="center"
        >
          <!-- passenger : direct payment confirmation --> 
          <v-col
            v-if="isPayment"
            align="center"
            class="font-weight-bold grey--text"
            cols="12"
          >
            {{ $t('directPay') }} :
          </v-col>
          <!-- driver : direct payment received confirmation -->
          <v-col
            v-else
            align="center"
            class="font-weight-bold"
          >
            {{ $tc('directPaymentReceived', directItems.length) }} :
          </v-col>
          <v-col
            align="left"
            cols="12"
          >
            <v-list shaped>
              <v-list-item
                v-for="(item, i) in directItems"
                :key="i"
              >
                <v-list-item-content class="grey--text">
                  <v-row justify="center">
                    <v-col
                      align="center"
                      cols="5"
                    >
                      <p class="my-n2">
                        {{ item.givenName }} {{ item.shortFamilyName }}
                      </p>
                    </v-col>
                    <v-col cols="4">
                      <p class="font-weight-bold my-n2">
                        {{ getAmount(item) }} €
                      </p>
                    </v-col>
                    <v-col class="my-n4">
                      <v-btn
                        color="secondary"
                        fab
                        x-small
                        :disabled="disabledComponent"
                        @click="removePayment(i, item)"
                      >
                        <v-icon>
                          mdi-trash-can
                        </v-icon>
                      </v-btn>
                    </v-col>
                  </v-row>
                </v-list-item-content>
              </v-list-item>
            </v-list>
          </v-col>
        </v-row>
        <v-divider v-if="ePay && ePayItems.length > 0 && directItems.length > 0" />
        <v-row
          v-if="ePay && ePayItems.length > 0"
          justify="center"
        >
          <v-col
            align="center"
            class="font-weight-bold"
            cols="12"
          >
            {{ $t('electronicPay') }} :
          </v-col>
          <v-col align="left">
            <v-list
              shaped
            >
              <v-list-item
                v-for="(item, i) in ePayItems"
                :key="i"
              >
                <v-list-item-content>
                  <v-row justify="center">
                    <v-col
                      align="center"
                      cols="5"
                    >
                      <p class="my-n2">
                        {{ item.givenName }} {{ item.shortFamilyName }}
                      </p>
                    </v-col>
                    <v-col cols="4">
                      <p class="font-weight-bold my-n2">
                        {{ getAmount(item) }} €
                      </p>
                    </v-col>
                    <v-col class="my-n4">
                      <v-btn
                        color="secondary"
                        fab
                        x-small
                        :disabled="disabledComponent"
                        @click="removePayment(i, item)"
                      >
                        <v-icon>
                          mdi-trash-can
                        </v-icon>
                      </v-btn>
                    </v-col>
                  </v-row>
                </v-list-item-content>
              </v-list-item>
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
          v-if="ePay && ePayItems.length > 0"
          justify="center"
        >
          <v-col align="center">
            <p class="text-h5">
              {{ $t('sumToPay', {price: sumToPay}) }}
            </p>
          </v-col>
        </v-row>
        <v-row
          v-if="ePayItems.length > 0 || directItems.length > 0"
          justify="center"
        >
          <p
            v-if="tipsEncouragement"
            style="display:block"
          >
            {{ $t('tipsEncouragement.text', {'platformName':platformName}) }}
            <a
              :href="tipsEncouragementLink"
              target="_blank"
              title="a"
            >{{ $t('tipsEncouragement.textLink') }}</a>.
          </p>
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
    </v-row>
    <v-row
      v-else
      justify="center"
    >
      <v-col
        cols="8"
      >
        <v-alert
          v-alert
          text
          type="success"
        >
          {{ $t('noMoreItems') }}
        </v-alert>
      </v-col>
    </v-row>
  </v-container>
</template>
<script>

import maxios from "@utils/maxios";
import moment from "moment";
import DayListChips from "@components/utilities/DayListChips";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/payment/Payment/";

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
    DayListChips
  },
  props: {
    paymentElectronicActive: {
      type: Boolean,
      default: false
    },
    frequency: {
      type: Number,
      default: null
    },
    type: {
      type: Number,
      default: null
    },
    selectedId: {
      type: Number,
      default: null
    },
    platformName: {
      type: String,
      default: ""
    },
    tipsEncouragement: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      locale: localStorage.getItem("X-LOCALE"),
      message:null,
      ePay: this.paymentElectronicActive,
      regular: this.frequency == 1 ? false : true,
      isPayment: this.type == 1 ? true : false,
      paymentItems: null,
      currentKey: 0,
      daysList: null,
      selectedWeekNumber: null,
      selectedWeekDays: null,
      menuSelectedWeekDays: false,
      periods: [],
      loading: false,
      disabledComponent: false,
      dialog: false,
      loadingPage: true,
      tipsEncouragementLink: this.$t('tipsEncouragement.link')
    };
  },
  computed: {
    computedSelectedWeekDays() {
      if (this.selectedWeekDays === null) return "";
      return this.$t("from")+moment(this.selectedWeekDays[0]).format(this.$t("ll"))+this.$t("to")+moment(this.selectedWeekDays[1]).format(this.$t("ll"))
    },
    maxDay() {
      return moment().startOf('week').subtract(1, 'days').format('Y-MM-DD');
    },
    currentItem: function() {
      return this.paymentItems ? this.paymentItems[this.currentKey] : null
    },
    previousItem: function() {
      return this.paymentItems && this.currentKey>0 && this.paymentItems[this.currentKey-1] ? this.paymentItems[this.currentKey-1] : null
    },
    nextItem: function() {
      return this.paymentItems && this.paymentItems[this.currentKey+1] ? this.paymentItems[this.currentKey+1] : null
    },
    itemDate: function() {
      return this.currentItem !== null && this.currentItem.date !== null ? moment(this.currentItem.date.date).format(this.$t("ll")) : null;
    },
    directItems: function() {
      var items = [];
      if (this.paymentItems !== null) {
        this.paymentItems.forEach((item, key) => {
          // mode 2 = direct payment
          if (item.mode==2) items.push(item);
        });
      }
      return items;
    },
    ePayItems: function() {
      var items = [];
      if (this.paymentItems !== null) {
        this.paymentItems.forEach((item, key) => {
          // mode 1 = electronic payment
          if (item.mode==1) items.push(item);
        });
      }
      return items;
    },
    sumToPay: function() {
      var sum = 0;
      if (this.ePayItems.length>0) {
        this.ePayItems.forEach((item, key) => {
          sum += parseFloat(this.getAmount(item));
        });
      }
      return sum;
    }
  },
  mounted () {
    // if regular, we search the first week where there are payments to confirm or pay
    if (this.frequency == 2) {
      this.getFirstWeek();
    } else {
      // we get the payments
      this.getPayments();
    }
  },
  created() {
    moment.locale(this.locale); 
  },
  methods: {
    
    // get the first week for which payments has to be made or collected
    getFirstWeek() {
      // we set params
      let params = {
        'id':this.selectedId
      }
      // we get all paymentItems
      maxios.post(this.$t("payments.getFirstWeek"), params)
        .then(res => {
          this.selectedWeekNumber = res.data.week;
          this.selectedWeekDays = [
            moment(this.selectedWeekNumber,'wwYYYY').startOf('week').format('Y-MM-DD'),
            moment(this.selectedWeekNumber,'wwYYYY').endOf('week').format('Y-MM-DD')
          ];
          this.getCalendar();
        })
        .then(() => this.getPayments());      
    },
    // get the different periods of carpools
    getCalendar() {
      // we set params
      let params = {
        'type':this.type
      }
      // we get all paymentItems
      maxios.post(this.$t("payments.getCalendar"), params)
        .then(res => {
          this.periods = res.data;
        });
    },
    // get the first day of the current year, used to be sure to have good week numbers in week picker
    getFirstDayOfYear() {
      return moment().startOf('year').format('E')
    },
    // restrict datepicker choices to the days carpooled
    allowedDates(val) {
      if (this.periods.length ==0) return false;
      var curDate = moment(val);
      var allowed = false;
      this.periods.forEach((item) => {
        var fromDate = moment(item.fromDate.date);
        var toDate = moment(item.toDate.date);
        if (curDate.isSameOrAfter(fromDate) && curDate.isSameOrBefore(toDate)) {
          // the items are returned using sunday as first day of week, so we use isoWeekday
          if (item.days.indexOf(parseInt(curDate.isoWeekday()))>-1) {
            allowed = true;
            return;
          }
        }
      });
      return allowed;
    },
    changeWeekDays() {
      this.selectedWeekNumber = ''+moment(this.selectedWeekDays[0]).isoWeek()+moment(this.selectedWeekDays[0]).year();
      let weekDays = [
        moment(this.selectedWeekDays[0]).startOf('week').format('Y-MM-DD'),
        moment(this.selectedWeekDays[0]).endOf('week').format('Y-MM-DD')
      ];
      this.selectedWeekDays = weekDays;
      this.menuSelectedWeekDays = false;
      this.getPayments();
    },
    getPayments() {
      this.loadingPage = true;
      // we set params
      let params = {
        'frequency':this.frequency,
        'type':this.type,
        'week':(this.selectedWeekNumber && this.selectedWeekNumber.length == 5) ? '0'+this.selectedWeekNumber : this.selectedWeekNumber
      }
      // we get all paymentItems
      maxios.post(this.$t("payments.getPayments"), params)
        .then(res => {
          var items = res.data;
          items.forEach((item, key) => {
            // we set dynamic parameters
            item.mode = null;
            if (item.id === this.selectedId) {              
              this.currentKey = key;              
            }
          });
          this.paymentItems = items;
          this.loadingPage = false;
        });
    },
    getAmount(item) {
      if (item.frequency == 1) {
        // punctual, we simply return the amount
        return item.amount;
      }
      // regular, we need to check each selected outward and return days
      let numberOutwardDays = 0;
      let numberReturnDays = 0;
      item.outwardDays.forEach((day) => {
        if (day.status == 1) numberOutwardDays++;
      });
      if (item.returnDays) {
        item.returnDays.forEach((day) => {
          if (day.status == 1) numberReturnDays++;
        });
      }
      return numberOutwardDays * item.outwardAmount +  numberReturnDays * item.returnAmount;
    },
    // method to update the dayslist of regular payment
    updateDaysList(daysList) {
      if (this.currentItem) {
        if (daysList.isOutward) {
          this.currentItem.outwardDays[0]['status'] = daysList.sun 
          this.currentItem.outwardDays[1]['status'] = daysList.mon
          this.currentItem.outwardDays[2]['status'] = daysList.tue
          this.currentItem.outwardDays[3]['status'] = daysList.wed
          this.currentItem.outwardDays[4]['status'] = daysList.thu
          this.currentItem.outwardDays[5]['status'] = daysList.fri
          this.currentItem.outwardDays[6]['status'] = daysList.sat
        } else if (!daysList.isOutward) {
          this.currentItem.returnDays[0]['status'] = daysList.sun 
          this.currentItem.returnDays[1]['status'] = daysList.mon
          this.currentItem.returnDays[2]['status'] = daysList.tue
          this.currentItem.returnDays[3]['status'] = daysList.wed
          this.currentItem.returnDays[4]['status'] = daysList.thu
          this.currentItem.returnDays[5]['status'] = daysList.fri
          this.currentItem.returnDays[6]['status'] = daysList.sat
        }
      }
      
    },
    // confirm the current item
    confirmPayment(mode) {
      // mode 1 : electronic payment
      // mode 2 : direct payment
      this.paymentItems[this.currentKey].mode = mode;
    },
    // remove a given item from the payment/confirmation list
    removePayment(i, item) {
      this.paymentItems.forEach((paymentItem, key) => {
        if (paymentItem.id == item.id) {
          paymentItem.mode = null;
        }
      });
    },
    
    // method to send confirmed or payed payments
    sendValidatedPayments() {
      this.loading = true;
      this.disabledComponent = true;
      let payments = [];
      this.paymentItems.forEach((paymentItem) => {
        // if punctual 
        if (this.frequency == 1) {
          if (paymentItem.mode) {
            payments.push({"id":paymentItem.id, "mode":paymentItem.mode, "status":1});
          } 
        } else {
          // if regular 
          // we add all available days of the outward travel
          paymentItem.outwardDays.forEach((day) => {
            // we check if we have made an action of payment on the paymentItem and we send it only if that's the case
            if (day.id && paymentItem.mode ) {
              payments.push({"id":day.id, "mode":paymentItem.mode, "status":day.status});
            }
          })
          // we add all available days of the return travel if return travel exist
          if (paymentItem.returnDays && paymentItem.returnDays.length > 0) {
            paymentItem.returnDays.forEach((day) => {
              if (day.id && paymentItem.mode ) {
                payments.push({"id":day.id, "mode":paymentItem.mode, "status":day.status});
              }
            })
          }
        }
      });
      //we post datas
      maxios.post(this.$t("payments.postPayments"), {
        "type": this.type,  
        "items": payments
      })
        .then(res => {
          if (res.data.redirectUrl) {
            window.location.href = res.data.redirectUrl;
          } else {
            window.location.href = this.$t("redirectAfterPayment");
          }
        })
        .catch((error) => {
          this.disabledComponent = false;
          this.loading = false;
          console.error(error);
        });
    },
    // method to send reported payments
    sendReport() {
      this.loading = true;
      this.currentItem.unpaidDate = moment();
      let payments = [];
      // if punctual 
      if (this.frequency == 1) {
        // we check if we have made an action of payment on the currentItem and we send it only if that's not the case
        if (!this.currentItem.mode) {
          payments.push({"id":this.currentItem.id, "mode":2, "status":3});
        }
      } else {
        // if regular 
        // we add all available days of the outward travel
        this.currentItem.outwardDays.forEach((day) => {
          if (day.id) {
            payments.push({"id":day.id, "mode":2, "status":day.status == 1 ? 3 : day.status});
          }
        })
        // we add all available days of the return travel if return travel exist
        if (this.currentItem.regularDays && this.currentItem.returnDays.length > 0) {
          this.currentItem.returnDays.forEach((day) => {
            if (day.id) {
              payments.push({"id":day.id, "mode":2, "status":day.status == 1 ? 3 : day.status});
            }
          })
        }
      }

      // we post data
      maxios.post(this.$t("payments.postPayments"), {
        "type": this.type,  
        "items": payments
      })
        .then(res => {
          this.loading = false;
          this.dialog = false;
        })
        .catch((error) => {
          console.error(error);
        });
    },
  }
};
</script>