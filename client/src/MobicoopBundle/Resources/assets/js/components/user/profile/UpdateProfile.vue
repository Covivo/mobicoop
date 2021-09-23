<template>
  <v-container>
    <!--SnackBar-->
    <v-snackbar
      v-model="snackbar"
      :color="(errorUpdate)?'error':'success'"
      top
    >
      {{ errorUpdate ? textSnackError : textSnackOk }}
      <v-btn
        color="white"
        text
        @click="snackbar = false"
      >
        <v-icon>mdi-close-circle-outline</v-icon>
      </v-btn>
    </v-snackbar>

    <v-alert
      v-if="savedCo2>0"
      type="success"
      outlined
    >
      {{ $t('savedCo2',{savedCo2:savedCo2}) }} CO<sup>2</sup>
    </v-alert>

    <!-- Main form -->
    <v-form
      ref="form"
      v-model="valid"
      lazy-validation
    >
      <v-card
        flat
        color="grey lighten-4"
        class="mb-8"
      >
        <v-card-title>
          {{ $t('titles.personnalInfos') }}
          <v-spacer />
          <v-btn
            color="primary"
            class="mb-8"
            rounded
            @click="dialogPublicProfile = true"
          >
            {{ $t('publicProfile.see') }}
          </v-btn>
          <v-btn
            v-if="gamificationActive"
            color="primary"
            class="mb-8 ml-2"
            rounded
            @click="dialogBadges = true"
          >
            {{ $t('badges.see') }}
          </v-btn>          
        </v-card-title>
        <v-card-text>
          <!-- Email -->
          <v-row
            no-gutters
          >
            <v-col 
              :cols="email && emailVerified ? '12' : '6'"
            >
              <v-text-field
                v-model="email"
                :label="$t('email.label')"
                type="email"
                class="email"
              >
                <template v-slot:append>
                  <v-tooltip 
                    color="info" 
                    top
                  >
                    <template v-slot:activator="{ on }">
                      <v-icon 
                        v-if="email && emailVerified" 
                        color="success"  
                        v-on="on"
                      >
                        mdi-check-circle-outline
                      </v-icon>
                      <v-icon 
                        v-else-if="email && !emailVerified" 
                        color="warning"  
                        v-on="on"
                      >
                        mdi-alert-circle-outline
                      </v-icon>
                    </template>
                    <span v-if="email && emailVerified">{{ $t('email.tooltips.verified') }}</span>
                    <span v-else-if="email && !emailVerified">{{ $t('email.tooltips.notVerified') }}</span>
                  </v-tooltip>
                </template>
              </v-text-field>
            </v-col>
            <v-col 
              v-if="email && !emailVerified"
              class="d-flex justify-center"
              cols="6"
            >
              <v-btn 
                rounded
                color="secondary" 
                :loading="loadingEmail" 
                @click="sendValidationEmail"
              >
                {{ !emailSended ? $t('email.buttons.label.generateEmail') : $t('email.buttons.label.generateEmailAgain') }}
              </v-btn>
            </v-col>
          </v-row>
          <v-row
            no-gutters
          >
            <!-- Telephone -->
            <v-col 
              :cols="telephone && phoneVerified ? '12' : '6'"
            >
              <v-text-field
                v-model="telephone"
                :label="$t('phone.label')"
                class="telephone"
                :rules="telephoneRules"
              >
                <template v-slot:append>
                  <v-tooltip 
                    color="info" 
                    top
                  >
                    <template v-slot:activator="{ on }">
                      <v-icon 
                        v-if="telephone && phoneVerified" 
                        color="success"  
                        v-on="on"
                      >
                        mdi-check-circle-outline
                      </v-icon>
                      <v-icon 
                        v-else-if="telephone && !phoneVerified" 
                        color="warning"  
                        v-on="on"
                      >
                        mdi-alert-circle-outline
                      </v-icon>
                    </template>
                    <span v-if="telephone && phoneVerified">{{ $t('phone.tooltips.verified') }}</span>
                    <span v-if="telephone && !phoneVerified">{{ $t('phone.tooltips.notVerified') }}</span>
                  </v-tooltip>
                </template>
              </v-text-field>
            </v-col>
            <v-col 
              v-if="telephone && displayPhoneVerification && !phoneVerified"
              class="d-flex justify-center"
              cols="6"
            >
              <v-btn 
                rounded 
                color="secondary" 
                :loading="loadingToken" 
                @click="generateToken"
              >
                {{ phoneToken == null ? $t('phone.buttons.label.generateToken') : $t('phone.buttons.label.generateNewToken') }}
              </v-btn>
            </v-col>
          </v-row>
          <v-row 
            v-if="phoneToken != null && telephone && !phoneVerified"
            no-gutters
          >
            <v-col cols="6">
              <v-text-field
                v-model="token"
                :rules="tokenRules"
                :label="$t('phone.validation.label')"
              />
            </v-col>
            <v-col 
              cols="6"
              class="d-flex justify-center"
            >
              <v-btn 
                rounded 
                color="secondary" 
                :loading="loadingValidatePhone" 
                @click="validateToken"
              >
                {{ $t('phone.buttons.label.validate') }}
              </v-btn>
            </v-col>
          </v-row>

          <v-row no-gutters>
            <!-- Phone display preferences -->
            <v-radio-group
              v-model="phoneDisplay['value']"
              :label="$t('phoneDisplay.label.general')" 
            >
              <v-radio
                v-for="(phDisplay, index) in phoneDisplays"
                :key="index"
                color="secondary"
                :label="phDisplay.label"
                :value="phDisplay.value"
              />
            </v-radio-group>
            <!--NewsSubscription-->
            <v-switch
              v-model="newsSubscription"
              :label="$t('news.label', {platform:platform})"
              inset
              color="secondary"
              @change="dialog = !newsSubscription"
            >
              <v-tooltip
                slot="append"
                left
                color="info"
                :max-width="'35%'"
              >
                <template v-slot:activator="{ on }">
                  <v-icon
                    justify="left"
                    v-on="on"
                  >
                    mdi-help-circle-outline
                  </v-icon>
                </template>
                <span>{{ $t('news.tooltip') }}</span>
              </v-tooltip>
            </v-switch>
            <!--NewsSubscription Confirmation Popup-->
            <v-dialog
              v-model="dialog"
              persistent
              max-width="500"
            >
              <v-card>
                <v-card-title class="text-h5">
                  {{ $t('news.confirmation.title') }}
                </v-card-title>
                <v-card-text v-html="$t('news.confirmation.content')" />
                <v-card-actions>
                  <v-spacer />
                  <v-btn
                    color="primary darken-1"
                    text
                    @click="dialog=false; newsSubscription=true"
                  >
                    {{ $t('no') }}
                  </v-btn>
                  <v-btn
                    color="secondary darken-1"
                    text
                    @click="dialog=false"
                  >
                    {{ $t('yes') }}
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-dialog>
          </v-row>

          <v-row
            no-gutters
            align="center"
          >
            <!-- Informations fields -->
            <v-col cols="7">
              <!--GivenName-->
              <v-text-field
                v-model="givenName"
                :label="$t('givenName.label')"
                class="givenName"
              />
              <!--FamilyName-->
              <v-text-field
                v-model="familyName"
                :label="$t('familyName.label')"
                class="familyName"
              />
              <!--Gender-->
              <v-select
                v-model="gender"
                :label="$t('gender.label')"
                :items="genders"
                item-text="gender"
                item-value="value"
              />
              <!-- Birthdate -->
              <v-menu
                ref="menu"
                v-model="menu"
                :close-on-content-click="false"
                transition="scale-transition"
                offset-y
                min-width="290px"
              >
                <template v-slot:activator="{ on }">
                  <v-text-field
                    :value="computedBirthdateFormat"
                    :label="$t('birthDay.label')"
                    :rules="[ birthdayRules.checkIfAdult, birthdayRules.required ]"
                    readonly
                    v-on="on"
                  />
                </template>
                <v-date-picker
                  ref="picker"
                  v-model="birthDay"
                  :max="maxDate()"
                  :locale="locale"
                  first-day-of-week="1"
                  @change="save"
                />
              </v-menu>
            </v-col>
            <!-- Avatar -->
            <v-col cols="5">
              <v-row
                justify="center"
                align="center"
              >
                <v-col
                  align="center"
                  class="d-flex justify-center"
                >
                  <v-avatar
                    color="lighten-3"
                    size="225px"
                  >
                    <img
                      id="avatar"
                      :src="urlAvatar"
                      alt="avatar"
                    >
                  </v-avatar>
                </v-col>
              </v-row>
              <v-row 
                justify="center"
              >
                <v-col
                  v-if="!displayFileUpload"
                  class="d-flex justify-center"
                >
                  <v-btn
                    :loading="loadingDelete"
                    color="warning"
                    class="ma-2 white--text pa-2 pr-3"
                    rounded
                    @click="avatarDelete"
                  >
                    {{ $t('avatar.delete.label') }}
                    <v-icon
                      right
                      dark
                    >
                      mdi-delete
                    </v-icon>
                  </v-btn>
                </v-col>

                <v-col
                  v-else
                  cols="10"
                  class="d-flex justify-center"
                >
                  <v-file-input
                    v-model="avatar"
                    :rules="avatarRules"
                    accept="image/png, image/jpeg, image/jpg, image/bmp"
                    :label="$t('avatar.label')"
                    prepend-icon="mdi-image"
                    :change="previewAvatar()"
                  />
                </v-col>
              </v-row>
            </v-col>
          </v-row>

          <v-row justify="center">
            <v-col
              class="d-flex justify-center"
            >
              <!--Save Button-->
              <v-btn
                class="button saveButton"
                color="secondary"
                rounded
                :disabled="!valid"
                :loading="loading"
                type="button"
                :value="$t('save')"
                @click="update"
              >
                {{ $t('save') }}
              </v-btn>
              <v-dialog
                v-model="dialogEmail"
                persistent
                max-width="450"
              >
                <v-card>
                  <v-card-title class="headline">
                    {{ $t('dialogEmail.title') }}
                  </v-card-title>
                  <v-card-text v-html="$t('dialogEmail.content')" />
                  <v-card-actions>
                    <v-spacer />
                    <v-btn
                      color="error"
                      text
                      @click="cancel"
                    >
                      {{ $t('dialogEmail.buttons.cancelUpdate') }}
                    </v-btn>
                    <v-btn
                      color="primary darken-1"
                      text
                      @click="validate"
                    >
                      {{ $t('dialogEmail.buttons.confirmUpdate') }}
                    </v-btn>
                  </v-card-actions>
                </v-card>
              </v-dialog>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-form>

    <!-- Address form -->
    <v-card
      flat
      color="grey lighten-4"
      class="mb-8"
    >
      <v-card-title>
        {{ $t('homeTown.label') }}
      </v-card-title>
      <v-card-text>
        <v-row no-gutters>
          <v-col>
            <!--GeoComplete-->
            <GeoComplete
              :url="geoSearchUrl"
              :label="$t('homeTown.label')"
              :token="user ? user.token : ''"
              :init-address="homeAddress"
              :display-name-in-selected="false"
              @address-selected="homeAddressSelected"
            />
          </v-col>
        </v-row>
        <v-row 
          no-gutters
          justify="center"
        >
          <v-col class="d-flex justify-center">
            <v-btn 
              rounded 
              color="secondary" 
              class="mt-4" 
              :disabled="disabledAddress" 
              :loading="loadingAddress" 
              type="button"
              @click="updateAddress"
            >
              {{ $t('address.update.label') }}
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>


    <!-- Password form -->
    <v-card
      flat
      color="grey lighten-4"
      class="mb-8"
    >
      <v-card-title>
        {{ $t('titles.password') }}
      </v-card-title>
      <v-card-text>
        <ChangePassword />
      </v-card-text>
    </v-card>

    <!-- Delete form -->
    <v-card
      flat
      color="grey lighten-4"
      class="mb-8"
    >
      <v-card-title>
        {{ $t('buttons.supprimerAccount') }}
      </v-card-title>
      <v-card-text>
        <v-row justify="center">
          <v-col class="d-flex justify-center">
            <v-dialog
              v-model="dialogDelete"
              width="500"
            >
              <template v-slot:activator="{ on }">
                <!--Delete button -->
                <v-btn
                  class="button"
                  color="error"
                  rounded
                  :disabled="!valid || disabledCreatedEvents || disabledOwnedCommunities"
                  :loading="loading"
                  type="button"
                  :value="$t('save')"
                  v-on="on"
                >
                  {{ $t('buttons.supprimerAccount') }}
                </v-btn>
              </template>

              <v-card>
                <v-card-title
                  v-if="hasCreatedEvents || hasOwnedCommunities"
                  class="text-h5 error--text"
                  primary-title
                >
                  {{ $t('dialog.titles.deletionImpossible') }}
                </v-card-title>
                <v-card-title
                  v-else
                  class="text-h5"
                  primary-title
                >
                  {{ $t('dialog.titles.deleteAccount') }}
                </v-card-title>

                <v-card-text>
                  <p
                    v-if="hasOwnedCommunities"
                    v-html="$t('dialog.content.errorCommunities')"
                  />
                  <p
                    v-else-if="hasCreatedEvents"
                    v-html="$t('dialog.content.errorEvents')"
                  />
                  <p
                    v-else
                    v-html="$t('dialog.content.deleteAccount')"
                  />
                </v-card-text>

                <v-divider />
                <v-card-actions v-if="hasCreatedEvents || hasOwnedCommunities">
                  <v-spacer />
                  <v-btn
                    color="primary darken-1"
                    text
                    @click="dialogDelete = false; newsSubscription = true"
                  >
                    {{ $t('dialog.buttons.close') }}
                  </v-btn>
                </v-card-actions>
                <v-card-actions v-else>
                  <v-spacer />
                  <v-btn
                    color="primary darken-1"
                    text
                    @click="dialogDelete = false; newsSubscription = true"
                  >
                    {{ $t('no') }}
                  </v-btn>
                  <v-btn
                    color="primary"
                    text
                    :href="$t('route.supprimer')"
                    @click="dialogDelete = false"
                  >
                    {{ $t('dialog.buttons.confirmDelete') }}
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-dialog>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- PUBLIC PROFILE DIALOG -->
    <v-dialog
      v-model="dialogPublicProfile"
      width="100%"
    >
      <v-card>
        <v-card-title class="headline grey lighten-2">
          {{ $t('publicProfile.title') }}
        </v-card-title>

        <v-card-text>
          <PublicProfile
            :user="user"
            :show-report-button="false"
            :age-display="ageDisplay"
          />
        </v-card-text>

        <v-divider />

        <v-card-actions>
          <v-spacer />
          <v-btn
            color="primary"
            text
            @click="dialogPublicProfile = false"
          >
            {{ $t('publicProfile.close') }}
          </v-btn>
        </v-card-actions>
      </v-card>      
    </v-dialog>    

    <!-- BADGES DIALOG -->
    <v-dialog
      v-model="dialogBadges"
      width="850px"
    >
      <v-card>
        <v-card-title class="headline grey lighten-2">
          {{ $t('badges.title') }}
        </v-card-title>

        <v-card-text>
          <Badges />
        </v-card-text>

        <v-divider />

        <v-card-actions>
          <v-spacer />
          <v-btn
            color="primary"
            text
            @click="dialogBadges = false"
          >
            {{ $t('badges.close') }}
          </v-btn>
        </v-card-actions>
      </v-card>      
    </v-dialog>
  </v-container>
</template>

<script>
import maxios from "@utils/maxios";
import moment from "moment";
import GeoComplete from "@js/components/utilities/GeoComplete";
import ChangePassword from "@components/user/profile/ChangePassword";
import PublicProfile from "@components/user/profile/PublicProfile";
import Badges from "@components/user/profile/Badges";
import { merge } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/profile/UpdateProfile/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/user/profile/UpdateProfile/";

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
    },
  },
  components: {
    PublicProfile,
    GeoComplete,
    ChangePassword,
    Badges
  },
  props: {
    avatarSize: {
      type: String,
      default: null
    },
    geoSearchUrl: {
      type: String,
      default: null
    },
    user: {
      type: Object,
      default: null
    },
    ageMin: {
      type: String,
      default: null
    },
    ageMax: {
      type: String,
      default: null
    },
    ageDisplay: {
      type: Boolean,
      default: true
    },
    platform: {
      type: String,
      default: ""
    } 
  },
  data() {
    return {
      dialog: false,
      dialogDelete: false,
      dialogPublicProfile: false,
      dialogBadges: false,
      snackbar: false,
      textSnackOk: this.$t('snackBar.profileUpdated'),
      textSnackError: this.$t("snackBar.passwordUpdateError"),
      errorUpdate: false,
      valid: true,
      errors: [],
      loading: false,
      loadingDelete: false,
      gender: this.user.gender,
      email: this.user.email,
      telephone: this.user.telephone,
      givenName: this.user.givenName,
      familyName: this.user.familyName,
      birthDay: this.user.birthDate ? this.user.birthDate.date : null,
      homeAddress: this.user.homeAddress ? this.user.homeAddress : null,
      phoneToken: this.user.phoneToken,
      phoneValidatedDate: this.user.phoneValidatedDate,
      emailValidatedDate: this.user.validatedDate,
      token: null,
      menu : false,
      genders:[
        { value: 1, gender: this.$t('gender.values.female')},
        { value: 2, gender: this.$t('gender.values.male')},
        { value: 3, gender: this.$t('gender.values.other')},
      ],
      phoneDisplay: {
        value: this.user.phoneDisplay
      },
      phoneDisplays:[
        { value: 1, label: this.$t('phoneDisplay.label.restricted')},
        { value: 2, label: this.$t('phoneDisplay.label.all')}
      ],
      avatar: null,
      avatarRules: [
        v => !v || v.size < this.avatarSize || this.$t("avatar.size")+" (Max "+(this.avatarSize/1000000)+"MB)"
      ],
      tokenRules: [
        v => (/^\d{4}$/).test(v) || this.$t("phone.token.inputError")
      ],
      telephoneRules: [
        v => (/^((\+)33|0)[1-9](\d{2}){4}$/).test(v) || this.$t("phone.errors.valid")
      ],
      birthdayRules : {
        required:  v => !!v || this.$t("birthDay.errors.required"),
        checkIfAdult : value =>{
          var d1 = new Date();
          var d2 = moment(value, "DD/MM/YYYY").toDate();

          var diff =(d1.getTime() - d2.getTime()) / 1000;
          diff /= (60 * 60 * 24);

          var diffYears =  Math.abs(Math.floor(diff/365.24) ) ;
          return diffYears >= 16 || this.$t("birthDay.errors.notadult")
        }
      },
      newsSubscription: this.user && this.user.newsSubscription !== null ? this.user.newsSubscription : null,
      urlAvatar: this.user.avatars[this.user.avatars.length-1],
      displayFileUpload: this.user.images.length == 0,
      phoneVerified: null,
      emailVerified: false,
      emailSended: false,
      loadingEmail: false,
      displayPhoneVerification: this.user.telephone ? true : false,
      loadingToken: false,
      loadingValidatePhone: false,
      disabledAddress: true,
      loadingAddress: false,
      ownedCommunities: null,
      createdEvents: null,
      hasCreatedEvents: false,
      hasOwnedCommunities: false,
      disabledOwnedCommunities: false,
      disabledCreatedEvents: false,
      locale: null,
      emailChanged: false,
      dialogEmail: false
    };
  },
  computed : {
    years () {
      const currentYear = new Date().getFullYear();
      const ageMin = Number(this.ageMin);
      const ageMax = Number(this.ageMax);
      return Array.from({length: ageMax - ageMin}, (value, index) => (currentYear - ageMin) - index)
    },
    computedBirthdateFormat () {
      if (this.birthDay) {
        return moment.utc(this.birthDay).format("DD/MM/YYYY");
      }
      return null;
    },
    savedCo2(){
      return Number.parseFloat(this.user.savedCo2  / 1000000 ).toPrecision(1);
    },
    gamificationActive(){
      return this.$store.getters['g/isActive'];
    },
  },
  watch: {
    menu (val) {
      val && setTimeout(() => (this.$refs.picker.activePicker = 'YEAR'))
    },
    telephone (val) {
      this.phoneToken = null;
      this.displayPhoneVerification = false;
    }, 
    email (val) {
      this.emailChanged = true;
    }
  },
  mounted() {
    this.locale = localStorage.getItem("X-LOCALE");
    moment.locale(this.locale);
    this.checkVerifiedPhone();
    this.checkVerifiedEmail();
    this.getOwnedCommunities();
    this.getCreatedEvents();
  },
  methods: {
    homeAddressSelected(address){
      this.homeAddress = address;
      this.disabledAddress = false;
    },
    save (date) {
      this.$refs.menu.save(date)
    },
   
    validate () {
      if (this.$refs.form.validate()) {
        this.checkForm();
        this.dialogEmail = false;
      }
    },
    update() {
      if (this.emailChanged) {
        this.dialogEmail = true;
      } else {
        this.validate();
      }
    },
    cancel () {
      window.location.reload();
    },
    checkForm () {
      this.loading = true;
      let updateUser = new FormData();
      updateUser.append("email", this.email);
      updateUser.append("familyName", this.familyName);
      updateUser.append("gender", this.gender);
      updateUser.append("givenName", this.givenName);
      updateUser.append("telephone", this.telephone);
      updateUser.append("birthDay", this.birthDay);
      updateUser.append("avatar", this.avatar);
      updateUser.append("newsSubscription", this.newsSubscription);
      updateUser.append("phoneDisplay", this.phoneDisplay.value);

      maxios
        .post(this.$t('route.update'), updateUser,
          {
            headers:{
              'content-type': 'multipart/form-data'
            }
          })
        .then(res => {
          this.errorUpdate = res.data.state;
          this.snackbar = true;
          this.loading = false;
          if (this.user.telephone != this.telephone) {
            this.phoneValidatedDate = null;
            this.phoneToken = null;
            this.displayPhoneVerification = true;
            this.checkVerifiedPhone();
            this.checkVerifiedEmail();
          }
          //this.urlAvatar = res.data.versions.square_800;
          // this.displayFileUpload = false; 
        })
        .catch(error => {
          window.location.reload();
        });
    },
    updateAddress () {
      this.loadingAddress = true;
      this.homeAddress.id = this.user.homeAddress ? this.user.homeAddress.id : null;
      maxios
        .post(this.$t('address.update.route'), this.homeAddress,
          {
            headers:{
              'content-type': 'application/json'
            }
          })
        .then(res => {
          this.homeAddress = res.data;
          this.loadingAddress = false;
          this.disabledAddress = true;
        });
    },
    avatarDelete () {
      this.loadingDelete = true;
      maxios
        .get(this.$t('avatar.delete.route'))
        .then(res => {
          this.errorUpdate = res.data.state;
          this.displayFileUpload = true;
          this.loadingDelete = false;
          this.urlAvatar = res.data[res.data.length-1];
        });
    },
    previewAvatar() {
      if(this.avatar) {
        let reader  = new FileReader();
        reader.addEventListener("load", function () {
          this.urlAvatar = reader.result; // UPDATE PREVIEW
        }.bind(this), false);
        reader.readAsDataURL(this.avatar); // FIRE LOAD EVENT
      } 
      // else {
      //   this.urlAvatar = this.user.avatars[this.user.avatars.length-1]; // RESET AVATAR
      // }
    },
    checkVerifiedPhone() {
      if (this.telephone !== null) {
        this.phoneVerified = this.phoneValidatedDate ? true : false;
      }
    },
    checkVerifiedEmail() {
      if (this.email !== null) {
        this.emailVerified = this.emailValidatedDate ? true : false;
      }
    },
    generateToken() {
      this.loadingToken = true;   
      maxios 
        .get(this.$t('phone.token.route'))
        .then(res => {
          if (res.data.state) {
            this.errorUpdate = true;
            this.textSnackError = this.$t('snackBar.tokenError');
            this.snackbar = true;
          }
          this.textSnackOk = this.$t('snackBar.tokenOk');
          this.snackbar = true;
          this.phoneToken = true;
          this.token = null;
          this.loadingToken = false;
        })
    },
    sendValidationEmail() {
      this.loadingEmail = true;   
      maxios 
        .get(this.$t('email.verificationRoute'))
        .then(res => {
          if (res.data.state) {
            this.errorUpdate = true;
            this.textSnackError = this.$t('snackBar.emailError');
            this.snackbar = true;
          }
          this.textSnackOk = this.$t('snackBar.emailOk');
          this.snackbar = true;
          this.emailSended = true;
          this.loadingEmail = false;   
        })
    },
    validateToken() {
      this.loadingValidatePhone = true; 
      maxios
        .post(this.$t('phone.validation.route'),
          {
            token: this.token,
            telephone: this.telephone
          },{
            headers:{
              'content-type': 'application/json'
            }
          })
        .then(res => {
          if(!res.data){
            this.errorUpdate = true;
            this.textSnackError = this.$t("snackBar.unknown");
            this.snackbar = true;
          }
          else{
            this.phoneVerified = true;
          }
          this.loadingValidatePhone = false;
        })
        // Todo create "emit" event to refresh the alerts
    },
    getOwnedCommunities() {
      let params = {
        'userId':this.user.id
      }
      this.disabledOwnedCommunities = true;
      maxios.post(this.$t("communities.route"), params)
        .then(res => {
          if (res.data.length > 0) {
            this.ownedCommunities = res.data;
            this.hasOwnedCommunities = true;
          }
          this.disabledOwnedCommunities = false;
        });
    },
    getCreatedEvents() {
      let params = {
        'userId':this.user.id
      }
      this.disabledCreatedEvents = true;
      maxios.post(this.$t("events.route"), params)
        .then(res => {
          if (res.data.length > 0) {
            this.createdEvents = res.data;
            this.createdEvents.forEach(createdEvent => {
              if (moment(createdEvent.toDate.date) >= moment(new Date())) {
                this.hasCreatedEvents = true;
              }
            });
          }
          this.disabledCreatedEvents = false;
        });
    },
    maxDate() {
      let maxDate = new Date();
      maxDate.setFullYear (maxDate.getFullYear() - this.ageMin);
      return maxDate.toISOString().substr(0, 10);
    }
  }
}
</script>
<style lang="scss" scoped>
  #avatar{
    width:auto !important;
  }
</style>
