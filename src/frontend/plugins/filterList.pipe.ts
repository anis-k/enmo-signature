import { Pipe, PipeTransform } from "@angular/core";



@Pipe({
	name: 'filterList'
})
export class FilterListPipe implements PipeTransform {

	transform(value: any, args: string): any {

		let filter = args.toLocaleLowerCase();
		return filter ? value.filter((basket:any) => basket.basket_name.toLocaleLowerCase().indexOf(filter) != -1) : value;
	}
}

/*@Pipe({
	name: 'filterShortcut'
})
export class FilterShortcutPipe implements PipeTransform {

	transform(value: any, args: string): any {

		let filter = args.toLocaleLowerCase();
		return filter ? value.filter((shortcut:any) => shortcut.label.toLocaleLowerCase().indexOf(filter) != -1) : value;
	}
}*/
