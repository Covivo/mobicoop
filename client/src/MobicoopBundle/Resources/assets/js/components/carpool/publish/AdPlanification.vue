<template>
  <v-container fluid>
    <v-form v-if="!regular">
      <!-- Punctual -->
      <!-- First row -->
      <v-row
        align="center"
        justify="center"
        dense
      >
        <!-- Outward date -->
        <v-col
          cols="5"
          offset="2"
        >
          <v-menu
            v-model="menuOutwardDate"
            :close-on-content-click="false"
            transition="scale-transition"
            offset-y
            full-width
            min-width="290px"
          >
            <template v-slot:activator="{ on }">
              <v-text-field
                :value="computedOutwardDateFormat"
                :label="$t('outwardDate.label')"
                readonly
                clearable
                v-on="on"
                @click:clear="clearOutwardDate"
              >
                <v-icon
                  slot="prepend"
                >
                  mdi-arrow-right-circle-outline
                </v-icon>
              </v-text-field>
            </template>
            <v-date-picker
              v-model="outwardDate"
              :locale="locale"
              no-title
              @input="menuOutwardDate = false"
              @change="change"
            />
          </v-menu>
        </v-col>

        <!-- Outward time -->
        <v-col
          cols="4"
        >
          <v-menu
            ref="menuOutwardTime"
            v-model="menuOutwardTime"
            :close-on-content-click="false"
            :return-value.sync="outwardTime"
            transition="scale-transition"
            offset-y
            full-width
            max-width="290px"
            min-width="290px"
          >
            <template v-slot:activator="{ on }">
              <v-text-field
                v-model="outwardTime"
                :label="$t('outwardTime.label')"
                prepend-icon=""
                readonly
                v-on="on"
              />
            </template>
            <v-time-picker
              v-if="menuOutwardTime"
              v-model="outwardTime"
              format="24hr"
              header-color="secondary"
              @click:minute="$refs.menuOutwardTime.save(outwardTime)"
              @change="change"
            />
          </v-menu>
        </v-col>
        <v-col
          cols="1"
        >
          <v-tooltip
            color="info"
            right
          >
            <template v-slot:activator="{ on }">
              <v-icon
                v-on="on"
              >
                mdi-help-circle-outline
              </v-icon>
            </template>
            <span>
              {{ infoMarginTime }}</span>
          </v-tooltip>
        </v-col>
      </v-row>

      <!-- Second row -->
      <v-row
        align="center"
        dense
      >
        <!-- Return trip ? -->
        <v-col
          cols="2"
        >
          <v-checkbox
            v-model="returnTrip"
            class="mt-0"
            :label="$t('returnTrip.label')"
            color="primary"
            hide-details
            @change="change"
          />
        </v-col>

        <!-- Return date -->
        <v-col
          cols="5"
        >
          <v-menu
            v-model="menuReturnDate"
            :close-on-content-click="false"
            transition="scale-transition"
            offset-y
            full-width
            min-width="290px"
          >
            <template v-slot:activator="{ on }">
              <v-text-field
                :value="computedReturnDateFormat"
                :label="$t('returnDate.label')"
                prepend-icon=""
                readonly
                :disabled="!returnTrip"
                v-on="on"
              >
                <v-icon
                  slot="prepend"
                >
                  mdi-arrow-left-circle-outline
                </v-icon>
              </v-text-field>
            </template>
            <v-date-picker
              v-model="returnDate"
              :locale="locale"
              no-title
              @input="menuReturnDate = false"
              @change="change"
            />
          </v-menu>
        </v-col>

        <!-- Return time -->
        <v-col
          cols="4"
        >
          <v-menu
            ref="menuReturnTime"
            v-model="menuReturnTime"
            :close-on-content-click="false"
            :return-value.sync="returnTime"
            transition="scale-transition"
            offset-y
            full-width
            max-width="290px"
            min-width="290px"
          >
            <template v-slot:activator="{ on }">
              <v-text-field
                v-model="returnTime"
                :label="$t('returnTime.label')"
                prepend-icon=""
                readonly
                :disabled="!returnTrip"
                v-on="on"
              />
            </template>
            <v-time-picker
              v-if="menuReturnTime"
              v-model="returnTime"
              format="24hr"
              header-color="secondary"
              @click:minute="$refs.menuReturnTime.save(returnTime)"
              @change="change"
            />
          </v-menu>
        </v-col>
        <v-col
          cols="1"
        >
          <v-tooltip
            color="info"
            right
          >
            <template v-slot:activator="{ on }">
              <v-icon
                v-on="on"
              >
                mdi-help-circle-outline
              </v-icon>
            </template>
            <span> {{ infoMarginTime }}
            </span>
          </v-tooltip>
        </v-col>
      </v-row>
    </v-form>
    
    <!-- Regular -->
    <v-form v-else>
      <!-- we have a maximum of 7 different schedules, we iterate on them -->
      <v-row
        v-for="item in activeSchedules"
        :key="item.id"
        align="center"
        justify="center"
      >
        <v-col
          cols="8"
        >
          <!-- Schedule -->
          <v-card>
            <!-- Checkboxes for the days -->
            <v-row
              align="center"
              justify="space-around"
              dense
            >
              <v-checkbox
                v-model="item.mon"
                label="L"
                color="primary"
                @change="change"
              />
              <v-checkbox
                v-model="item.tue"
                label="Ma"
                color="primary"
                @change="change"
              />
              <v-checkbox
                v-model="item.wed"
                label="Me"
                color="primary"
                @change="change"
              />
              <v-checkbox
                v-model="item.thu"
                label="J"
                color="primary"
                @change="change"
              />
              <v-checkbox
                v-model="item.fri"
                label="V"
                color="primary"
                @change="change"
              />
              <v-checkbox
                v-model="item.sat"
                label="S"
                color="primary"
                @change="change"
              />
              <v-checkbox
                v-model="item.sun"
                label="D"
                color="primary"
                @change="change"
              />
            </v-row>

            <!-- Times -->
            <v-row
              align="center"
              justify="space-around"
              dense
            >
              <!-- Outward time -->
              <v-col
                cols="5"
              >
                <v-menu
                  v-model="item.menuOutwardTime"
                  :close-on-content-click="false"
                  transition="scale-transition"
                  offset-y
                  full-width
                  max-width="290px"
                  min-width="290px"
                >
                  <template v-slot:activator="{ on }">
                    <v-text-field
                      v-model="item.outwardTime"
                      :label="$t('regularOutwardTime.label')"
                      prepend-icon=""
                      readonly
                      v-on="on"
                    >
                      <v-icon
                        slot="prepend"
                      >
                        mdi-arrow-right-circle
                      </v-icon>
                    </v-text-field>
                  </template>
                  <!-- 
                    we can't use $refs with v-for : https://vuejs.org/v2/guide/components-edge-cases.html#Accessing-Child-Component-Instances-amp-Child-Elements 
                    because $refs are not reactive, we have to use a custom method closeOutwardTime() which will close the menu
                    -->
                  <v-time-picker
                    v-if="item.menuOutwardTime"
                    v-model="item.outwardTime"
                    format="24hr"
                    header-color="secondary"
                    @click:minute="closeOutwardTime(item.id)"
                    @change="change"
                  />
                </v-menu>
              </v-col>
              <v-col
                cols="1"
              >
                <v-tooltip
                  color="info"
                  right
                >
                  <template v-slot:activator="{ on }">
                    <v-icon
                      v-on="on"
                    >
                      mdi-help-circle-outline
                    </v-icon>
                  </template>
                  <span>{{ infoMarginTime }}
                  </span>
                </v-tooltip>
              </v-col>

              <!-- Return time -->
              <v-col
                cols="5"
              >
                <v-menu
                  v-model="item.menuReturnTime"
                  :close-on-content-click="false"
                  transition="scale-transition"
                  offset-y
                  full-width
                  max-width="290px"
                  min-width="290px"
                >
                  <template v-slot:activator="{ on }">
                    <v-text-field
                      v-model="item.returnTime"
                      :label="$t('regularReturnTime.label')"
                      :hint="$t('ui.form.optional')"
                      persistent-hint
                      prepend-icon=""
                      readonly
                      v-on="on"
                    >
                      <v-icon
                        slot="prepend"
                      >
                        mdi-arrow-left-circle
                      </v-icon>
                    </v-text-field>
                  </template>
                  <!-- 
                    we can't use $refs with v-for : https://vuejs.org/v2/guide/components-edge-cases.html#Accessing-Child-Component-Instances-amp-Child-Elements 
                    because $refs are not reactive, we have to use a custom method close() which will close the menu
                    -->
                  <v-time-picker
                    v-if="item.menuReturnTime"
                    v-model="item.returnTime"
                    format="24hr"
                    header-color="secondary"
                    @click:minute="closeReturnTime(item.id)"
                    @change="change"
                  />
                </v-menu>
              </v-col>
              <v-col
                cols="1"
              >
                <v-tooltip
                  color="info"
                  right
                >
                  <template v-slot:activator="{ on }">
                    <v-icon
                      v-on="on"
                    >
                      mdi-help-circle-outline
                    </v-icon>
                  </template>
                  <span>{{ infoMarginTime }}
                  </span>
                </v-tooltip>
              </v-col>
            </v-row>
            
            <!-- Remove schedule -->
            <v-row
              align="center"
              justify="end"
              dense
            >
              <v-col
                cols="2"
              >
                <v-btn
                  v-if="item.id>1"
                  text
                  icon
                  @click="removeSchedule(item.id)"
                >
                  <v-icon>
                    mdi-delete-circle
                  </v-icon>
                </v-btn>
              </v-col>
            </v-row>
          </v-card>
        </v-col>
      </v-row>

      <!-- Add schedule -->
      <v-row
        v-if="!schedules[6].visible"
        align="center" 
        justify="center"
        dense
      >
        <v-col
          cols="8"
        >
          <v-btn
            text
            icon
            @click="addSchedule"
          >
            <v-icon>
              mdi-plus-circle-outline
            </v-icon>
          </v-btn>
          {{ $t('addSchedule') }}
        </v-col>
      </v-row>
    </v-form>
  </v-container>
</template>

<script>
import moment from "moment";
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/carpool/publish/AdPlanification.json";
import TranslationsClient from "@clientTranslations/components/carpool/publish/AdPlanification.json";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  components: {
  },
  props: {
    regular: {
      type: Boolean,
      default: false
    },
    initOutwardDate: {
      type: String,
      default: null
    },
    initOutwardTime: {
      type: String,
      default: null
    },
    defaultMarginTime: {
      type: Number,
      default: null
    }
  },
  data() {
    return {
      outwardDate: this.initOutwardDate,
      outwardTime: this.initOutwardTime,
      returnDate: null,
      returnTime: null,
      menuOutwardDate: false,
      menuOutwardTime: false,
      menuReturnDate: false,
      menuReturnTime: false,
      returnTrip: false,
      marginTime: this.defaultMarginTime,
      locale: this.$i18n.locale,
      // todo : refactor the following horror with default types :)
      schedules: [
        {
          id:1,
          visible: true,
          mon: false,
          tue: false,
          wed: false,
          thu: false,
          fri: false,
          sat: false,
          sun: false,
          outwardTime: null,
          returnTime: null,
          menuOutwardTime: false,
          menuReturnTime: false
        },
        {
          id:2,
          visible: false,
          mon: false,
          tue: false,
          wed: false,
          thu: false,
          fri: false,
          sat: false,
          sun: false,
          outwardTime: null,
          returnTime: null,
          menuOutwardTime: false,
          menuReturnTime: false
        },
        {
          id:3,
          visible: false,
          mon: false,
          tue: false,
          wed: false,
          thu: false,
          fri: false,
          sat: false,
          sun: false,
          outwardTime: null,
          returnTime: null,
          menuOutwardTime: false,
          menuReturnTime: false
        },
        {
          id:4,
          visible: false,
          mon: false,
          tue: false,
          wed: false,
          thu: false,
          fri: false,
          sat: false,
          sun: false,
          outwardTime: null,
          returnTime: null,
          menuOutwardTime: false,
          menuReturnTime: false
        },
        {
          id:5,
          visible: false,
          mon: false,
          tue: false,
          wed: false,
          thu: false,
          fri: false,
          sat: false,
          sun: false,
          outwardTime: null,
          returnTime: null,
          menuOutwardTime: false,
          menuReturnTime: false
        },
        {
          id:6,
          visible: false,
          mon: false,
          tue: false,
          wed: false,
          thu: false,
          fri: false,
          sat: false,
          sun: false,
          outwardTime: null,
          returnTime: null,
          menuOutwardTime: false,
          menuReturnTime: false
        },
        {
          id:7,
          visible: false,
          mon: false,
          tue: false,
          wed: false,
          thu: false,
          fri: false,
          sat: false,
          sun: false,
          outwardTime: null,
          returnTime: null,
          menuOutwardTime: false,
          menuReturnTime: false
        }
      ]
    };
  },
  computed: {
    computedOutwardDateFormat() {
      moment.locale(this.locale);
      return this.outwardDate
        ? moment(this.outwardDate).format(this.$t("ui.i18n.date.format.fullDate"))
        : "";
    },
    computedReturnDateFormat() {
      moment.locale(this.locale);
      return this.returnDate
        ? moment(this.returnDate).format(this.$t("ui.i18n.date.format.fullDate"))
        : "";
    },
    activeSchedules() {
      return this.schedules.filter(function(schedule) {
        return schedule.visible;
      });
    },
    infoMarginTime() {
      return this.$t("marginTooltip",{margin: this.marginTime/60})
    }
  },
  watch: {
    initOutwardDate() {
      this.outwardDate = this.initOutwardDate;
    }
  },
  methods: {
    change() {
      let validSchedules = JSON.parse(JSON.stringify(this.activeSchedules)); // little tweak to deep copy :)
      for (var i=0;i<validSchedules.length;i++) {
        if (!((validSchedules[i].mon || validSchedules[i].tue || validSchedules[i].wed || validSchedules[i].thu || validSchedules[i].fri || validSchedules[i].sat || validSchedules[i].sun) && validSchedules[i].outwardTime)) {
          validSchedules.splice(i);
        } else {
          delete validSchedules[i].id;
          delete validSchedules[i].visible;
          delete validSchedules[i].menuOutwardTime;
          delete validSchedules[i].menuReturnTime;
        }        
      }
      this.$emit("change", {
        outwardDate: this.outwardDate,
        outwardTime: this.outwardTime,
        returnDate: this.returnDate,
        returnTime: this.returnTime,
        returnTrip: this.returnTrip,
        schedules: validSchedules
      });
    },
    clearOutwardDate() {
      this.outwardDate = null;
      this.change();
    },
    closeOutwardTime(id) {
      for (var i in this.schedules) {
        if (this.schedules[i].id == id) {
          this.schedules[i].menuOutwardTime = false;
          break;
        }
      }
    },
    closeReturnTime(id) {
      for (var i in this.schedules) {
        if (this.schedules[i].id == id) {
          this.schedules[i].menuReturnTime = false;
          break;
        }
      }
    },
    removeSchedule(id) {
      for (var i in this.schedules) {
        if (this.schedules[i].id == id) {
          this.schedules[i].visible = false;
          break;
        }
      }
      this.change();
    },
    addSchedule() {
      for (var i in this.schedules) {
        if (!this.schedules[i].visible) {
          this.schedules[i].visible = true;
          break;
        }
      }
      this.change();
    }
  }
};
</script>