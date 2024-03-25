declare namespace App.Data {
export type AddressData = {
id: any | number;
type: App.Enums.AddressType;
street: string;
city: string;
state: any | string;
country: any | string;
};
export type ContactData = {
cid: string;
first_name: string;
last_name: string;
title: App.Enums.Title | any;
job_title: any | string | null;
firm: App.Data.FirmData | any;
emails: Array<App.Data.EmailData> | Array<any>;
};
export type ContactFullData = {
id: any | number;
cid: any | string;
first_name: string;
last_name: string;
nickname: any | string | null;
middle_name: any | string | null;
job_title: any | string | null;
bio: any | string | null;
phones: Array<App.Data.PhoneData> | Array<any>;
emails: Array<App.Data.EmailData> | Array<any>;
firm: App.Data.FirmData | any;
title: App.Enums.Title;
};
export type EmailData = {
id: any | number;
email: string;
is_primary_email: boolean;
};
export type FirmData = {
id: any | number;
fid: any | string;
name: string;
slogan: any | string;
address: App.Data.AddressData | any;
url: any | string;
};
export type PhoneData = {
id: any | number;
country_code: any | string;
number: string;
type: App.Enums.PhoneType | any;
is_primary_phone: boolean;
formatted: any | string;
};
export type UserData = {
first_name: string;
last_name: string;
email: any | string;
name: any | string;
};
}
declare namespace App.Enums {
export type AddressType = 'home' | 'work';
export type PhoneType = 'mobile' | 'work' | 'home' | 'fax';
export type Title = 'mr' | 'mrs' | 'ms' | 'sr' | 'prof' | 'dr';
}
