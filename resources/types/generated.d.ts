declare namespace App.Data {
export type AddressData = {
id: any | number;
type: App.Enums.AddressType;
state: any | string;
country: any | string;
street: string;
city: string;
};
export type BoardData = {
id: any | number | null;
name: string;
project_id: any | number;
tasks: Array<App.Data.TaskData> | Array<any> | null;
};
export type ContactData = {
cid: string;
first_name: string;
last_name: string;
title: App.Enums.Title | any;
job_title: any | string | null;
firm: App.Data.FirmData | any | null;
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
firm: App.Data.FirmData | any | null;
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
slogan: any | string | null;
address: App.Data.AddressData | any | null;
url: any | string | null;
name: any | string | null;
tags: Array<App.Data.TagData> | Array<any> | null;
};
export type PhoneData = {
id: any | number;
country_code: any | string;
type: App.Enums.PhoneType | any;
is_primary_phone: boolean;
formatted: any | string;
number: string;
};
export type ProjectData = {
pid: string;
name: string;
created_at: string;
due_date: string;
status: string;
contact: App.Data.ContactData;
};
export type ProjectFullData = {
pid: any | string | null;
name: string;
created_at: any | string | null;
due_date: string;
status: string;
description: any | string | null;
contact_id: any | string | null;
contact: App.Data.ContactData | any;
boards: Array<App.Data.BoardData> | Array<any> | null;
};
export type TagData = {
id: any | number;
name: Array<any> | string;
slug: any | Array<any> | string;
type: any | string;
order_column: any | number;
};
export type TaskData = {
id: any | string | null;
name: string;
priority: string;
is_completed: boolean;
description: any | string | null;
board_id: number;
position: any | number | null;
assigned_to: number;
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
