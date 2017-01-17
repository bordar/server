package lib.Borhan.notification;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import lib.Borhan.output.Console;

import com.borhan.client.types.BorhanHttpNotification;


/**
 * This processor is require for events handling.
 * All handlers should register to it, and it should fire the matching ones when an event arrive.  
 */
public class Processor {
	
	private Console console;
	
	/** Mapping handlers which aren't event dependent */
	private Map<HandlerProcessType, List<BaseNotificationHandler>> handlers = new HashMap<HandlerProcessType, List<BaseNotificationHandler>>();

	/**
	 * Constructor
	 * @param console
	 */
	public Processor(Console console) {
		this.console = console;
	}
	
	/**
	 * Registers single handler to a single event
	 * @param handler The handler we wish to register
	 */
	public void registerHandler(BaseNotificationHandler handler) {
		HandlerProcessType processType = handler.getType();
		if(!handlers.containsKey(processType))
			handlers.put(processType, new ArrayList<BaseNotificationHandler>());
		handlers.get(processType).add(handler);
	}

	/**
	 * Handle single notification
	 * @param httpNotification The handle we wish to handle
	 */
	public void handleNotification(BorhanHttpNotification httpNotification) {
		fireHandlers(httpNotification, handlers.get(HandlerProcessType.PRE_PROCESS));
		fireHandlers(httpNotification, handlers.get(HandlerProcessType.PROCESS));
		fireHandlers(httpNotification, handlers.get(HandlerProcessType.POST_PROCESS));
	}

	/**
	 * This function fires the matching handlers from a given list
	 * @param httpNotification The notification
	 * @param curHandlers The list of handlers
	 */
	private void fireHandlers(BorhanHttpNotification httpNotification,
			List<BaseNotificationHandler> curHandlers) {
		if(curHandlers != null)
			for (BaseNotificationHandler handler : curHandlers) {
				if(handler.shouldHandle(httpNotification)) {
					console.write("Handler: " + handler.getClass().getName());
					handler.handle(httpNotification);
				}
			}
	}
}
