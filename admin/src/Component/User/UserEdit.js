import React, {useState} from 'react';
import {useDataProvider} from 'react-admin';

import GeocompleteInput from "../Utilities/geocomplete";
import {UserRenderer, addressRenderer} from '../Utilities/renderers'
//import bcrypt from 'bcryptjs';

import {
    Edit,
    TabbedForm,
    FormTab,
    TextInput,
    SelectInput,
    DateInput,
    email,
    Button,
    regex,
    ReferenceArrayInput,
    SelectArrayInput,
    BooleanInput,
    ReferenceInput,
    ReferenceField,
    FunctionField,
    useTranslate
} from 'react-admin';
import Close from '@material-ui/icons/Close';
import Check from '@material-ui/icons/Check';
import {makeStyles} from '@material-ui/core/styles'

const useStyles = makeStyles({
    spacedHalfwidth: {width: "45%", marginBottom: "1rem", display: 'inline-flex', marginRight: '1rem'},
    footer: {marginTop: "2rem"},
});


const DynamicForm = ({record}) => {

        const classes = useStyles();
        const [checkbox, updateCheckbox] = React.useState([]);
        const [input, updateInput] = React.useState([]);
        const [selectBox, updateSelectBox] = React.useState([]);
        const [radio, updateRadio] = React.useState([]);
        const apiUrl = process.env.REACT_APP_API;


        const [user, setUser] = useState(null);
        const dataProvider = useDataProvider();

        React.useEffect(function effectFunction() {
            const token = localStorage.getItem('token');
            fetch(apiUrl+'/users/1/structures', {
                headers: new Headers({
                    'Authorization': 'Bearer ' + token
                })
            })
                .then(res => res.json())
                .then(
                    (result) => {
                        result.structures.map(struct => {
                            struct.structureProofs.map(proof => {
                                if (proof.checkbox) {
                                    updateCheckbox([...checkbox, proof]);
                                } else if (proof.input) {
                                    updateInput([...input, proof])
                                } else if (proof.selectbox) {
                                    let options = proof.options.split(",");
                                    let temp = [];
                                    options.map(function (val, index) {
                                        let obj = {
                                            id: index,
                                            name: val
                                        }
                                        temp.push(obj)
                                    })
                                    proof.options = temp
                                    updateSelectBox([...selectBox, proof])
                                } else if (proof.radio) {
                                    let options = proof.options.split(",");
                                    let temp = [];
                                    options.map(function (val, index) {
                                        temp.push(val)
                                    })
                                    updateRadio([...radio, temp])
                                }
                            })
                        })
                    },
                    (error) => {
                        console.log(error)

                    }
                )
        }, []);
        console.log(checkbox);
        const float = {
            float: "left",
            width: "78px"
        }
        return (
            <FormTab label={''}>
                {
                    checkbox.map((value, index) =>
                        <BooleanInput key={index} initialValue={true} label={value.label} source={value.label}/>
                    )

                }
                {
                    input.map((value, index) =>
                        <TextInput fullWidth required source={value.label} label={value.label}/>
                    )

                }
                {
                    selectBox.map((value, index) =>
                        <SelectInput required source={value.label} label={value.label}
                                     choices={value.options}
                                     formClassName={classes.spacedHalfwidth}/>
                    )

                }
                {
                    radio.map((value, index) =>
                        value.map((name, index) =>
                            <label data-testid="label">
                                {name}
                                <input data-testid="element" name={'radio'} value={name} type="radio"/>
                            </label>
                        )
                    )

                }
                {
                    <div style={float}>
                        <Button
                            label={'Accept'}
                            color="secondary"
                            onClick={''}
                        >
                            <Check/>
                        </Button>
                    </div>
                }
                {
                    <Button
                        label={'Reject'}
                        color="secondary"
                        onClick={''}
                    >
                        <Close/>
                    </Button>

                }
            </FormTab>
        )

    }
;

const UserEdit = props => {
    const classes = useStyles();
    const translate = useTranslate();
    const instance = process.env.REACT_APP_INSTANCE_NAME;
    const required = (message = translate('custom.alert.fieldMandatory')) =>
        value => value ? undefined : message;

    const genderChoices = [
        {id: 1, name: translate('custom.label.user.choices.women')},
        {id: 2, name: translate('custom.label.user.choices.men')},
        {id: 3, name: translate('custom.label.user.choices.other')},
    ];
    const smoke = [
        {id: 0, name: translate('custom.label.user.choices.didntSmoke')},
        {id: 1, name: translate('custom.label.user.choices.didntSmokeCar')},
        {id: 2, name: translate('custom.label.user.choices.smoke')},
    ];
    const musique = [
        {id: false, name: translate('custom.label.user.choices.withoutMusic')},
        {id: true, name: translate('custom.label.user.choices.withMusic')},
    ];

    const bavardage = [
        {id: false, name: translate('custom.label.user.choices.dontTalk')},
        {id: true, name: translate('custom.label.user.choices.talk')},
    ];
    const phoneDisplay = [
        {id: 0, name: translate('custom.label.user.phoneDisplay.forAll')},
        {id: 1, name: translate('custom.label.user.phoneDisplay.forCarpooler')},
    ];

    const validateRequired = [required()];
    const emailRules = [required(), email()];

    return (
        <Edit {...props} title={translate('custom.label.user.title.edit')}>
            <TabbedForm initialValues={{news_subscription: true}}>
                <FormTab label={translate('custom.label.user.indentity')}>
                    <TextInput fullWidth required source="email" label={translate('custom.label.user.email')}
                               validate={emailRules}/>

                    <TextInput fullWidth required source="familyName" label={translate('custom.label.user.familyName')}
                               validate={validateRequired} formClassName={classes.spacedHalfwidth}/>
                    <TextInput fullWidth required source="givenName" label={translate('custom.label.user.givenName')}
                               validate={validateRequired} formClassName={classes.spacedHalfwidth}/>
                    <SelectInput required source="gender" label={translate('custom.label.user.gender')}
                                 choices={genderChoices} validate={validateRequired}
                                 formClassName={classes.spacedHalfwidth}/>
                    <DateInput required source="birthDate" label={translate('custom.label.user.birthDate')}
                               validate={validateRequired} formClassName={classes.spacedHalfwidth}/>

                    <TextInput required source="telephone" label={translate('custom.label.user.telephone')}
                               validate={validateRequired} formClassName={classes.spacedHalfwidth}/>

                    <BooleanInput fullWidth
                                  label={translate('custom.label.user.newsSubscription', {instanceName: instance})}
                                  source="news_subscription" formClassName={classes.spacedHalfwidth}/>

                    <SelectInput fullWidth source="phoneDisplay"
                                 label={translate('custom.label.user.phoneDisplay.visibility')} choices={phoneDisplay}
                                 formClassName={classes.spacedHalfwidth}/>

                    <ReferenceArrayInput required label={translate('custom.label.user.roles')} source="rolesIds"
                                         reference="permissions/roles" validate={validateRequired}
                                         formClassName={classes.footer}>
                        <SelectArrayInput optionText="name"/>
                    </ReferenceArrayInput>

                    <ReferenceInput label={translate('custom.label.user.territory')} source="userTerritories"
                                    reference="territories">
                        <SelectInput optionText="name"/>
                    </ReferenceInput>

                    <BooleanInput initialValue={true} label={translate('custom.label.user.accepteReceiveEmail')}
                                  source="newsSubscription"/>

                </FormTab>
                <FormTab label={translate('custom.label.user.preference')}>
                    <SelectInput fullWidth source="music" label={translate('custom.label.user.carpoolSetting.music')}
                                 choices={musique} formClassName={classes.spacedHalfwidth}/>
                    <TextInput fullWidth source="musicFavorites"
                               label={translate('custom.label.user.carpoolSetting.musicFavorites')}
                               formClassName={classes.spacedHalfwidth}/>
                    <SelectInput fullWidth source="chat" label={translate('custom.label.user.carpoolSetting.chat')}
                                 choices={bavardage} formClassName={classes.spacedHalfwidth}/>
                    <TextInput fullWidth source="chatFavorites"
                               label={translate('custom.label.user.carpoolSetting.chatFavorites')}
                               formClassName={classes.spacedHalfwidth}/>
                    <SelectInput fullWidth source="smoke" label={translate('custom.label.user.carpoolSetting.smoke')}
                                 choices={smoke} formClassName={classes.spacedHalfwidth}/>
                </FormTab>

                <FormTab label={translate('custom.label.user.solidaty_eligibility')}>
                    <DynamicForm/>
                </FormTab>


            </TabbedForm>
        </Edit>
    );

};
export default UserEdit
